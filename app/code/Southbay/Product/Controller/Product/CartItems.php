<?php

namespace Southbay\Product\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

class CartItems extends Action
{
    protected $jsonFactory;
    protected $checkoutSession;

    public function __construct(
        Context         $context,
        JsonFactory     $jsonFactory,
        CheckoutSession $checkoutSession
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getItemsCollection()->getItems();

        $cartItems = array_map(function ($item) {
            return [
                'sku' => strstr($item->getSku(), '/', true) ?: $item->getSku()
            ];
        }, $items ?: []);

        return $this->jsonFactory->create()->setData(array_values($cartItems));
    }

}
