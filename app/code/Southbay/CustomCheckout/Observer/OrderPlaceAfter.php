<?php

namespace Southbay\CustomCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        // $order = $observer->getEvent()->getOrder();

        /*
        $customer = $order->getCustomer();

        if ($customer && $customer->getCustomAttribute('southbay_requiere_auth')) {
            $requiereAuth = $customer->getCustomAttribute('southbay_requiere_auth')->getValue();

            if ($requiereAuth == false) {
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus(Order::STATE_PROCESSING)
                    ->addStatusToHistory(
                        Order::STATE_PROCESSING,
                        __('La orden se ha cambiado automáticamente a procesado debido a southbay_requiere_auth.')
                    );
            }
        }
        */
    }
}
