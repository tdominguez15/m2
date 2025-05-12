<?php

namespace Southbay\CustomCheckout\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    private $log;

    private $checkoutSession;

    private $southbay_helper;

    private $orderItemFactory;

    public function __construct(Context                                $context,
                                CheckoutSession                        $checkoutSession,
                                \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
                                \Southbay\Product\Helper\Data          $southbay_helper,
                                LoggerInterface                        $log)
    {
        parent::__construct($context);
        $this->log = $log;
        $this->checkoutSession = $checkoutSession;
        $this->southbay_helper = $southbay_helper;
        $this->orderItemFactory = $orderItemFactory;
    }

    public function getLastOrderId()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        return $order->getId();
    }

    public function getPoNumber()
    {
        $po_number = null;
        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote) {
                $payment = $quote->getPayment();
                if (is_null($payment->getMethod())) {
                    $payment->setMethod('purchaseorder');
                }
                if (empty($payment->getPoNumber())) {
                    $payment->setPoNumber(time());
                }
                $po_number = $payment->getPoNumber();
            }
        } catch (\Exception $e) {
            $this->log->error('Error getting po number', ['e' => $e]);
        }
        return $po_number;
    }

    public function getQuoteItems()
    {
        $result = [
            'valid' => [],
            'invalid' => []
        ];

        try {
            $quote = $this->checkoutSession->getQuote();
            $items = $quote->getItems();

            foreach ($items as $item) {
                $_item = $quote->getItemById($item->getItemId());
                $config = $this->southbay_helper->getProductSeasonConfig($_item->getProduct());
                if (empty($config)) {
                    $result['invalid'][] = $_item;
                } else {
                    $result['valid'][] = $_item;
                }
            }
        } catch (\Exception $e) {
            $this->log->error('getQuoteItems: Error getting quote items', ['e' => $e]);
        }

        return $result;
    }

    public function getCartSummaryCount()
    {
        $count = 0;

        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote) {
                $items = $quote->getItems();
                if ($items) {
                    $count = count($items);
                } else {
                    $this->log->info('getCartSummaryCount: Not items');
                }
            } else {
                $this->log->info('getCartSummaryCount: Not quote');
            }
        } catch (\Exception $e) {
            $this->log->error('getCartSummaryCount: Error getting cart summary count', ['e' => $e]);
        }

        $this->log->info('getCartSummaryCount: Result', ['count' => $count]);

        return $count;
    }

    public function checkOrderItems(\Magento\Sales\Model\Order $order)
    {
        $items = $order->getItems();
        $update_base_row_total = [];
        $update_variants = [];
        $info = [];
        $map_items = [];
        $items_to_add = [];

        foreach ($items as $item) {
            $product = $item->getProduct();
            $option = $item->getData('product_options');

            if ($item->getBaseRowTotal() == 0) {
                $update_base_row_total[] = $item;
            } else {
                $items_to_add[] = $item;
            }

            $sku = $item->getSku();
            $sku = explode('/', $sku)[0];
            $size = $product->getData('southbay_size');

            if (!isset($map_items[$sku])) {
                $map_items[$sku] = [];
                $map_items[$sku]['default'] = $product->getId();
            }

            $map_items[$sku][$size] = $product->getId();

            if (isset($option['info_buyRequest']['month_delivery_date_1']) ||
                isset($option['info_buyRequest']['month_delivery_date_2']) ||
                isset($option['info_buyRequest']['month_delivery_date_3'])
            ) {
                $total_general = [];

                $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_1'] ?? null, $total_general);
                $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_2'] ?? null, $total_general);
                $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_3'] ?? null, $total_general);

                $info[$sku] = $total_general;
            }
        }

        foreach ($info as $sku => $total_general) {
            $keys = array_keys($total_general);

            foreach ($keys as $key) {
                if (!isset($map_items[$sku][$key])) {
                    $update_variants[] = ['sku' => $sku, 'product_id' => $map_items[$sku]['default'], 'size' => $key, 'total' => $total_general[$key]];
                }
            }
        }

        foreach ($update_base_row_total as $item) {
            $sku = $item->getSku();
            $sku = explode('/', $sku)[0];
            $product = $item->getProduct();
            $size = $product->getData('southbay_size');

            if (!isset($info[$sku])) {
                continue;
            }

            if (!isset($info[$sku][$size])) {
                continue;
            }

            $total = $info[$sku][$size];
            $item->setQtyOrdered($total);
            $item->setRowTotal($total * $item->getPrice());
            $item->setBaseRowTotal($item->getRowTotal());
            $item->setRowTotalInclTax($item->getRowTotal());
            $item->setBaseRowTotalInclTax($item->getRowTotal());

            if ($item->getBaseRowTotal() > 0) {
                $items_to_add[] = $item;
                $order->setTotalQtyOrdered($order->getTotalQtyOrdered() + $total);
            }
        }

        $map_products = [];

        foreach ($update_variants as $fix) {
            $sku = $fix['sku'];
            if (!isset($map_products[$sku])) {
                /**
                 * @var \Magento\Catalog\Model\Product $parent
                 */
                $parent = $this->southbay_helper->findParentProduct($fix['product_id']);
                $parent->setStoreId($order->getStoreId());
                $children = $this->southbay_helper->getProductVariants($parent);

                $map_products[$sku] = [];

                foreach ($children as $child) {
                    $size = $child->getData('southbay_size');
                    $map_products[$sku][$size] = $child;
                }
            }

            if (!isset($map_products[$sku][$fix['size']])) {
                continue;
            }

            $_product = $map_products[$sku][$fix['size']];

            $_item = $this->orderItemFactory->create();

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
            $_item->setStoreId($order->getStoreId());
            $_item->setQtyOrdered($fix['total']);

            $_item->setRowTotal($_item->getQtyOrdered() * $_item->getPrice());
            $_item->setBaseRowTotal($_item->getRowTotal());

            $_item->setRowTotalInclTax($_item->getRowTotal());
            $_item->setBaseRowTotalInclTax($_item->getRowTotal());

            $info = [
                'form_key' => time(),
                'qty' => 0,
                'ignore' => true,
                'product' => $_product->getId()
            ];
            $_item->setProductOptions([
                'info_buyRequest' => $info
            ]);

            $items_to_add[] = $_item;

            $order->setTotalQtyOrdered($order->getTotalQtyOrdered() + $_item->getQtyOrdered());
        }

        $order->setItems($items_to_add);
    }

    private function sumTotalGeneral($info_buyRequest, &$total_general)
    {
        if (!$info_buyRequest) {
            return;
        }

        foreach ($info_buyRequest as $key => $value) {
            if (!isset($total_general[$key])) {
                $total_general[$key] = $value;
            } else {
                $total_general[$key] += $value;
            }
        }
    }
}
