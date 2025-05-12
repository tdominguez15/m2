<?php

namespace Southbay\ApproveOrders\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class AfterPlaceOrder
{
    private LoggerInterface $log;
    private ConfigStoreRepository $configStoreRepository;
    private StoreManagerInterface $storeManager;
    private SouthbayApproveOrderHelper $approveOrderHelper;

    public function __construct(
        LoggerInterface $log,
        ConfigStoreRepository $configStoreRepository,
        StoreManagerInterface $storeManager,
        SouthbayApproveOrderHelper $approveOrderHelper
    ) {
        $this->log = $log;
        $this->configStoreRepository = $configStoreRepository;
        $this->storeManager = $storeManager;
        $this->approveOrderHelper = $approveOrderHelper;
    }

    /**
     * @param OrderService $subject
     * @param OrderInterface $result
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(OrderService $subject, OrderInterface $result, OrderInterface $order): OrderInterface
    {
        try {
            $isAtOnce = $this->configStoreRepository->isAtOnce($this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            $this->log->critical("No se pudo instanciar store, Order: " . $order->getIncrementId());
            $this->log->critical($e);
            return $result;
        }

        if ($isAtOnce) {
            $this->approveOrderHelper->scheduleNotification($result);
        }

        return $result;
    }
}
