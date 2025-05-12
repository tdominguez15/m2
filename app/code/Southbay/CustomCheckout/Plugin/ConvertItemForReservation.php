<?php

namespace Southbay\CustomCheckout\Plugin;

use Magento\InventorySales\Model\PlaceReservationsForSalesEvent;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Psr\Log\LoggerInterface;
use \Southbay\Product\Helper\Data;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;

class ConvertItemForReservation
{

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var Data
     */
    private $southbayHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigStoreRepository
     */
    private $configStoreRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param LoggerInterface $log
     * @param Data $southbayHelper
     * @param CheckoutSession $checkoutSession
     * @param ConfigStoreRepository $configStoreRepository
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
    */

    public function __construct(
        LoggerInterface $log,
        Data $southbayHelper,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        CheckoutSession $checkoutSession,
        ConfigStoreRepository $configStoreRepository,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager

    ) {
        $this->log = $log;
        $this->southbayHelper = $southbayHelper;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->checkoutSession = $checkoutSession;
        $this->configStoreRepository = $configStoreRepository;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param PlaceReservationsForSalesEvent $subject
     * @param array|ItemToSellInterface[] $items
     * @param SalesChannelInterface $salesChannel
     * @param SalesEventInterface $salesEvent
     * @return array
     */
    public function beforeExecute(PlaceReservationsForSalesEvent $subject, array $items, SalesChannelInterface $salesChannel, SalesEventInterface $salesEvent): array
    {
        $storeId = $this->storeManager->getStore()->getId();
        $isAtOnce = $this->configStoreRepository->isAtOnce($storeId);
        if(!$isAtOnce || $salesEvent->getType() != 'order_placed'){
           return [$items, $salesChannel, $salesEvent];
        }

        $itemToSell = [];
        $modifiedProducts = [];

        $quote = $this->checkoutSession->getQuote();
        $quoteItems = $quote->getAllItems();
        $quote_map_products = [];
        foreach ($quoteItems as $item) {
            $product = $item->getProduct();
            $quote_map_products[$product->getId()] = $product;
        }
        foreach ($quoteItems as $item) {
            $product = $item->getProduct();

            $quote_product = $quote_map_products[$item->getProductId()];
            $totales = $this->southbayHelper->getTotalFromSeasonConfigBySize($quote_product);
            //
            if (empty($totales)) {
                throw new \Exception(__('El producto ' . $product->getSku() . ' no tiene cantidades'));
            }
            $parent = $this->southbayHelper->findParentProduct($product->getId());
            $children = $this->southbayHelper->getProductVariants($parent);
            $oldSeasonConfig = $this->southbayHelper->getProductSeasonConfig($item->getProduct());
            foreach ($children as $_product) {
                $size = $_product->getSouthbaySize();
                if(!isset($totales[$size])){
                    continue;
                }
                $qtyOrdered = $totales[$size]['general'];
                if($qtyOrdered < 1){
                    continue;
                }
                $multiploCompra = $parent->getData('southbay_purchase_unit');

                $maxStockQty = $this->southbayHelper->getStockByProduct($_product,$storeId)['qty'];


                if(!empty($multiploCompra && $multiploCompra > 1 )){
                    $qtyOrdered = $this->getMultiploMayor($qtyOrdered,$multiploCompra);
                    $maxStockQty = $this->getMultiploMayor($maxStockQty,$multiploCompra);
                }
                if (!isset($qtyOrdered) && ($qtyOrdered == 0 ||  $maxStockQty  == 0)) {
                    unset($oldSeasonConfig['atonce'][$size]);
                    $modifiedProducts[] = $_product->getSku() . ' (0)';
                } else {
                    // si solicita mas que el stock maximo, toma el stock maximo, sino toma lo solicitado, Agregar modificacion al customOption

                        if($qtyOrdered > $maxStockQty){
                            $itemToSell[] = $this->itemsToSellFactory->create([
                                'sku' => $_product->getSku(),
                                'qty' => -(float)$maxStockQty,
                            ]);
                            $modifiedProducts[] = $_product->getSku() . ' (' . $maxStockQty . ')';
                            $oldSeasonConfig['atonce'][$size] = $maxStockQty;
                        }
                        else {
                            $itemToSell[] = $this->itemsToSellFactory->create([
                                'sku' => $_product->getSku(),
                                'qty' => -(float)$qtyOrdered,
                            ]);
                        }
                    }

                }
                $existingOption = $item->getOptionByCode('season_qty');
                if ($existingOption) {
                    $existingOption->setValue(json_encode($oldSeasonConfig));
                }
            }
        if (!empty($modifiedProducts)) {
            $this->messageManager->addWarningMessage(
                __('Los siguientes productos fueron modificados por disponiblidad y la cantidad solicitada quedo de la siguiente forma: %1', implode(', ', $modifiedProducts))
            );
        }

        return [$itemToSell, $salesChannel, $salesEvent];
    }
    public function getMultiploMayor($number,$multiplo){

        if ($multiplo > 0) {
            return floor($number / $multiplo) * $multiplo;
        }
        else return 0;
    }
}


