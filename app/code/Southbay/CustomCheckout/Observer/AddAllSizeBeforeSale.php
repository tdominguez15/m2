<?php

namespace Southbay\CustomCheckout\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\ItemFactory;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Magento\Framework\Message\ManagerInterface;

class AddAllSizeBeforeSale implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $log;

    private $southbay_helper;

    private $quoteItemFactory;

    private $session;
    private $customerSession;

    private $configStoreRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    private $southbay_checkout_helper;

    public function __construct(LoggerInterface                      $log,
                                ItemFactory                          $quoteItemFactory,
                                \Magento\Customer\Model\Session      $customerSession,
                                CheckoutSession                      $session,
                                ConfigStoreRepository                $configStoreRepository,
                                \Southbay\Product\Helper\Data        $southbay_helper,
                                \Southbay\CustomCheckout\Helper\Data $southbay_checkout_helper,
                                ManagerInterface                     $messageManager)
    {
        $this->log = $log;
        $this->southbay_helper = $southbay_helper;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->configStoreRepository = $configStoreRepository;
        $this->messageManager = $messageManager;
        $this->southbay_checkout_helper = $southbay_checkout_helper;
    }

    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $observer->getEvent()->getData('order');

        if ($order->getId()) {
            return;
        }

        if ($order->getStatus() != 'pending') {
            return;
        }

        if ($order->getStatus() == \Magento\Sales\Model\Order::STATE_CANCELED) {
            return;
        }

        $this->log->info('Init AddAllSizeBeforeSale. Order status: ' . $order->getStatus());

        $config_store = $this->configStoreRepository->findByStoreId($order->getStoreId());

        $this->log->info('Info store', ['config' => $config_store->getFunctionCode(), 'store_id' => $order->getStoreId()]);

        if ($config_store->getFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_FUTURES) {
            $season = $this->southbay_helper->getCurrentSeason($order->getStoreId());

            if (is_null($season)) {
                throw new \Exception(__('No hay una temporada activa'));
            }

            $order->setExtOrderId($season->getId());
        }

        $quote = $this->session->getQuote();
        $order->setCustomerNote($quote->getCustomerNote());

        if (!empty($quote->getExtShippingInfo())) {
            $order->addCommentToStatusHistory($quote->getExtShippingInfo(), false, false);
        }

        $order->setExtCustomerId($this->customerSession->getSoldtoId());

        $quote_items = $quote->getAllItems();
        $quote_map_products = [];

        foreach ($quote_items as $item) {
            $product = $item->getProduct();
            $quote_map_products[$product->getId()] = $product;
        }

        $items = $order->getAllItems();
        $new_items = [];
        $total_qty = 0;
        $grandTotal = 0;
        $modifiedProducts = [];

        foreach ($items as $item) {
            $_save_config = null;
            $product = $item->getProduct();
            $quote_product = $quote_map_products[$product->getId()];

            $config = $this->southbay_helper->getProductSeasonConfig($quote_product);
            $totales = $this->southbay_helper->getTotalFromSeasonConfigBySize($quote_product);
            $original_qty = $item->getQtyOrdered();

            if (empty($totales)) {
                throw new \Exception(__('El producto ' . $product->getSku() . ' no tiene cantidades'));
            }

            $parent = $this->southbay_helper->findParentProduct($product->getId());
            $children = $this->southbay_helper->getProductVariants($parent);

            /**
             * @var \Magento\Catalog\Model\Product $_product
             */
            foreach ($children as $_product) {
                $size = $_product->getSouthbaySize();

                $this->log->info('Intentando agregar/actualizar talle:', ['size' => $size]);

                if (!isset($totales[$size])) {
                    $this->log->info('No hay totales para el talle:', ['size' => $size]);
                } else if ($totales[$size]['general'] == 0) {
                    $this->log->info('El total es cero para el talle:', ['size' => $size]);
                    if ($product->getId() === $_product->getId()) {
                        $_save_config = $config;
                        $this->log->info('Intentando remover item', ['id' => $item->getItemId(), 'size' => $size]);
                    }
                } else {
                    $add_item = false;
                    if ($product->getId() === $_product->getId()) {
                        $_save_config = $config;
                        $_item = $item;
                    } else {
                        $this->log->info('Agregando talle...', ['price' => $_product->getPrice()]);
                        $add_item = true;
                        $_item = $this->quoteItemFactory->create();

                        $_item->setProductType($_product->getTypeId());
                        $_item->setProductId($_product->getId());

                        $_item->setSku($_product->getSku());
                        $_item->setName($_product->getName());

                        $_item->setPriceInclTax($_product->getPrice());
                        $_item->setBasePriceInclTax($_product->getPrice());

                        $_item->setPrice($_product->getPrice());
                        $_item->setBasePrice($_product->getPrice());

                        $_item->setOriginalPrice($_product->getPrice());
                        $_item->setBaseOriginalPrice($_product->getPrice());
                        $_item->setStoreId($quote->getStoreId());
                    }
                    $multiploCompra = $parent->getData('southbay_purchase_unit');
                    $_item->setQtyOrdered($totales[$size]['general']);
                    // verificar multiplos y actualizar json de options y cantidades de orden6
                    $totalQtyMultiplo = null;
                    $this->updateItemQuantitiesBasedOnMultiples($_item, $totales, $multiploCompra, $_product, $config, $_save_config, $modifiedProducts, $size, $totalQtyMultiplo);

                    $_item->setRowTotal($_item->getQtyOrdered() * $_item->getPrice());
                    $_item->setBaseRowTotal($_item->getRowTotal());

                    $_item->setRowTotalInclTax($_item->getRowTotal());
                    $_item->setBaseRowTotalInclTax($_item->getRowTotal());

                    $_product->addCustomOption('season_item_qty', json_encode($totales[$size]['months']));
                    $_item->setProductOptions($totales[$size]['months']);

                    if (!is_null($_save_config)) {
                        $this->log->info('Se agrega la configuracion general a item:', ['sku' => $product->getSku()]);
                        $_product->addCustomOption('season_qty', json_encode($_save_config));

                        $info = $config;
                        $info['form_key'] = time();
                        $info['qty'] = $totalQtyMultiplo ?? $original_qty;
                        $info['product'] = $_product->getId();

                        $_item->setProductOptions([
                            'info_buyRequest' => $info
                        ]);

                        $_save_config = null;
                    } else {
                        $info = [
                            'form_key' => time(),
                            'qty' => 0,
                            'ignore' => true,
                            'product' => $_product->getId()
                        ];
                        $_item->setProductOptions([
                            'info_buyRequest' => $info
                        ]);
                    }

                    $this->log->info('sky qty', ['sku' => $_product->getSku(), 'qty' => $_item->getQtyOrdered()]);
                    $new_items[] = $_item;
                    $total_qty += $_item->getQtyOrdered();
                    $grandTotal += (float)$_item->getPrice() * $_item->getQtyOrdered();
                }
            }
        }

        $order->setItems($new_items);
        $order->setTotalQtyOrdered($total_qty);
        $order->setGrandTotal($grandTotal);
        $order->setBaseSubtotal($grandTotal);
        $order->setBaseGrandTotal($grandTotal);
        $order->setSubtotal($grandTotal);

        $this->southbay_checkout_helper->checkOrderItems($order);

        $this->log->info('End AddAllSizeBeforeSale...');
        if (!empty($modifiedProducts)) {
            $this->messageManager->addWarningMessage(
                __('Los siguientes productos fueron modificados no poder ser fraccionados y la cantidad solicitada es de la siguiente forma: %1', implode(', ', $modifiedProducts))
            );
        }
    }

    // devuelve el multiplo siguiente del numero
    public function getMultiploMayor($number, $multiplo)
    {
        if ($multiplo > 0) {
            $remainder = $number % $multiplo;
            if ($remainder == 0) {
                return $number; // $number ya es múltiplo de $multiplo
            } else {
                return $number + ($multiplo - $remainder);
            }
        } else {
            return 0;
        }
    }

    /**
     * Actualiza las cantidades de un artículo basado en múltiplos de compra.
     *
     * @param \Magento\Sales\Model\Order\Item $_item
     * @param array $totales
     * @param int $multiploCompra
     * @param \Magento\Catalog\Model\Product $_product
     * @param array $config
     * @param array|null $_save_config
     * @param array $modifiedProducts
     * @param string $size
     */
    private function updateItemQuantitiesBasedOnMultiples(
        $_item,
        &$totales,
        $multiploCompra,
        $_product,
        &$config,
        &$_save_config,
        &$modifiedProducts,
        $size,
        &$totalQtyMultiplo
    )
    {
        if (!empty($multiploCompra) && $multiploCompra > 1 && $_item->getQtyOrdered() % $multiploCompra != 0) {
            $totalQtyMultiplo = 0;

            foreach ($totales as $talle => $totalesTalles) {
                foreach ($totalesTalles['months'] as $monthKey => $monthQty) {
                    $mayorMultiploPosible = $this->getMultiploMayor($monthQty, $multiploCompra);
                    $totales[$talle]['months'][$monthKey] = $mayorMultiploPosible;
                    $totales[$talle]['general'] = $mayorMultiploPosible;
                    $config[$monthKey][$talle] = $mayorMultiploPosible;

                    if (!is_null($_save_config)) {
                        $_save_config[$monthKey][$talle] = $mayorMultiploPosible;
                    }

                    $totalQtyMultiplo += $mayorMultiploPosible;

                    if ($monthQty != $mayorMultiploPosible) {
                        $modifiedProducts[] = $_product->getSku() . ' (' . $mayorMultiploPosible . ')';
                    }
                }
            }

            $_item->setQtyOrdered($totales[$size]['general']);
        }
    }
}
