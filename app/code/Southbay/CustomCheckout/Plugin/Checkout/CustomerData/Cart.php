<?php

namespace Southbay\CustomCheckout\Plugin\Checkout\CustomerData;

use Southbay\CustomCheckout\Helper\Data as Southbay_CustomCheckout_Helper;

class Cart
{
    private $log;
    private $helper;
    private $southbay_helper;

    public function __construct(Southbay_CustomCheckout_Helper $helper,
                                \Southbay\Product\Helper\Data  $southbay_helper,
                                \Psr\Log\LoggerInterface       $log)
    {
        $this->log = $log;
        $this->helper = $helper;
        $this->southbay_helper = $southbay_helper;
    }

    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, array $result)
    {
        $summary_count = $this->helper->getCartSummaryCount();
        $result['summary_count'] = $summary_count;

        if (isset($result['items'])) {
            $items = $result['items'];

            foreach ($items as &$item) {
                $parent = $this->southbay_helper->findParentProduct($item['product_id']);
                $southbay_sport = $this->southbay_helper->getProductValues($item['product_sku'], ['southbay_sport']);

                $item['product_southbay_sport'] = $southbay_sport['southbay_sport'];

                if(is_null($parent)) {
                    $item['product_southbay_sku'] = $item['product_sku'];
                } else {
                    $item['product_southbay_sku'] = $parent->getSku();
                }
            }

            $result['items'] = $items;
        }
        return $result;
    }
}
