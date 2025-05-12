<?php

namespace Southbay\Product\Controller\Product;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;


class All implements HttpGetActionInterface
{
    private $resultJsonFactory;
    private $context;
    private $southbay_helper;

    private $productRepository;

    private $log;

    private $cart;

    private $resultRedirectFactory;

    public function __construct(\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
                                \Southbay\Product\Helper\Data                        $southbay_helper,
                                LoggerInterface                                      $log,
                                \Magento\Checkout\Model\Cart                         $cart,
                                \Magento\Framework\App\Action\Context                $context)
    {
        $this->context = $context;
        $this->southbay_helper = $southbay_helper;
        $this->log = $log;
        $this->cart = $cart;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        


        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart');

        return $resultRedirect;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    private function getRequest(): \Magento\Framework\App\RequestInterface
    {
        return $this->context->getRequest();
    }
}
