<?php

namespace Southbay\Product\Controller\Product;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;


class Size implements HttpGetActionInterface
{
    private $resultJsonFactory;
    private $context;
    private $southbay_helper;

    private $productRepository;

    private $log;

    private $cart;

    public function __construct(JsonFactory                           $resultJsonFactory,
                                ProductRepositoryInterface            $productRepository,
                                \Southbay\Product\Helper\Data         $southbay_helper,
                                LoggerInterface                       $log,
                                \Magento\Checkout\Model\Cart          $cart,
                                \Magento\Framework\App\Action\Context $context)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->context = $context;
        $this->southbay_helper = $southbay_helper;
        $this->productRepository = $productRepository;
        $this->log = $log;
        $this->cart = $cart;
    }

    public function execute()
    {
        $list = [];
        $options = [];

        $request = $this->getRequest();
        $productId = $request->getParam('product_id');

        try {
            $product = $this->productRepository->getById($productId);
            $items = $this->cart->getItems();

            $values = [];

            /**
             * @var \Magento\Quote\Model\Quote\Item $quote_item
             */
            foreach ($items as $quote_item) {
                $_product = $quote_item->getProduct();
                if ($this->southbay_helper->isParent($_product->getId(), $productId)) {
                    $values = $this->southbay_helper->getProductSeasonConfig($_product);
                    break;
                }
            }

            $options = $this->southbay_helper->getChildrenLabels($product);
            $southbay_season_months = $this->southbay_helper->getMonthForDeliveryFromCurrent();
            $productReleaseDate = $product->getSouthbayReleaseDate();
            $map = [];

            foreach ($southbay_season_months as $key => $month) {
                if ($productReleaseDate !== null) {
                    $releaseDate = new \DateTime($productReleaseDate);
                    $seasonMonthDate = new \DateTime($month['date']);

                    //  check if product has release date higher than season month
                    if ($releaseDate > $seasonMonthDate) {
                        unset($southbay_season_months[$key]);
                        continue;
                    }
                }

                $item = [
                    'code' => $month['code'],
                    'title' => $month['label'],
                    'options' => []
                ];

                $option_total = 0;

                foreach ($options as $option) {
                    $_item = [
                        'code' => $option['value'],
                        'qty' => 0
                    ];

                    if (isset($values[$month['code']]) && isset($values[$month['code']][$option['value']])) {
                        $_item['qty'] = intval($values[$month['code']][$option['value']]);
                    }

                    $item['options'][] = $_item;

                    if (!isset($map[$option['value']])) {
                        $map[$option['value']] = [];
                    }

                    $option_total += $_item['qty'];
                    $map[$option['value']][$month['code']] = $_item['qty'];
                }

                $item['options'][] = [
                    'code' => 'total',
                    'qty' => $option_total
                ];

                $list[] = $item;
            }

            if (!empty($southbay_season_months)) {
                $item = [
                    'code' => 'total',
                    'title' => 'Total',
                    'options' => []
                ];

                $option_total = 0;

                foreach ($options as $option) {
                    $keys = array_keys($map[$option['value']]);
                    $total = 0;

                    foreach ($keys as $key) {
                        $total += $map[$option['value']][$key];
                    }

                    $item['options'][] = [
                        'code' => $option['value'],
                        'qty' => $total
                    ];

                    $option_total += $total;
                }

                $item['options'][] = [
                    'code' => 'total',
                    'qty' => $option_total
                ];

                $list[] = $item;
            }
        } catch (\Exception $e) {
            $this->log->error('Error getting sizes', ['error' => $e]);
        }

        $result = $this->resultJsonFactory->create();

        return $result->setData([
            'list' => $list,
            'header' => $options
        ]);
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    private function getRequest(): \Magento\Framework\App\RequestInterface
    {
        return $this->context->getRequest();
    }
}
