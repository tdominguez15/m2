<?php

namespace Southbay\CustomCheckout\Plugin\Checkout\Quote;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class OrderObservation
{
    private $session;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $session
    )
    {
        $this->session = $session;
    }

    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
                                                             $cartId,
        \Magento\Quote\Model\Quote\Payment                   $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface             $billingAddress = null
    )
    {
        $order_observation = '';
        $additionalInformation = $paymentMethod->getAdditionalData();
        if (isset($additionalInformation['order_observation'])) {
            $order_observation = $additionalInformation['order_observation'];
        }

        $quote = $this->session->getQuote();
        $quote->setCustomerNote($order_observation);

        return [$cartId, $paymentMethod, $billingAddress];
    }
}
