<?php

namespace Southbay\Product\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;


class SizeAtOnce implements HttpGetActionInterface
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

            //$options = $this->southbay_helper->getChildrenLabels($product);
            $options = $this->southbay_helper->getChildrenLabelsAndStock($product);
            $row = $this->southbay_helper->getMonthForDeliveryFromCurrent()[0];
            $stock = $this->southbay_helper->getChildrenStock($product);

            $map = [];

                $item = [
                    'code' => $row['code'],
                    'title' => $row['label'],
                    'options' => []
                ];

                $option_total = 0;

                foreach ($options as $key => &$option) {
                    $_item = [ 'code' => $option['value'],
                        'qty' => 0,
                        'salableQty' => $option['salableQty']
                    ];
                    $option['stock'] = $stock[$option['value']]->getQty();

                    if (isset($values[$row['code']]) && isset($values[$row['code']][$option['value']])) {
                        $_item['qty'] = intval($values[$row['code']][$option['value']]);
                    }

                    $item['options'][] = $_item;

                    if (!isset($map[$option['value']])) {
                        $map[$option['value']] = [];
                    }

                    $option_total += $_item['qty'];
                    $map[$option['value']][$row['code']] = $_item['qty'];
                }

                $item['options'][] = [
                    'code' => 'total',
                    'qty' => $option_total
                ];

                $list[] = $item;
  //          }


        } catch (\Exception $e) {
            $this->log->error('Error getting sizes', ['error' => $e]);
        }

        $result = $this->resultJsonFactory->create();

        return $result->setData([
            'list' => $list,
            'header' => array_values($options)
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
