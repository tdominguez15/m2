<?php

namespace Southbay\CustomCustomer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\CustomCustomer\Api\Data\OrderEntryNotificationConfigInterface;
use Southbay\CustomCustomer\Api\Data\OrderEntryNotificationInterface;
use Southbay\CustomCustomer\Api\Data\OrderEntryRepConfigInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\ReturnProduct\Helper\SendEmailNotification;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\Collection as ConfigStoreCollection;

class SouthbayApproveOrderHelper extends AbstractHelper
{
    private $collectionFactory;
    private $configStoreRepository;
    private $repConfigCollectionFactory;
    private $notificationCollectionFactory;

    private $factory;

    private $userCollectionFactory;

    private $soldToRepository;

    private $orderCollectionFactory;
    private $helperSendEmailNotification;

    private $configStoreCollection;

    private $storeManager;

    private $productHelper;

    private $storesAtonce = null;
    private $_source_code_cache = [];

    private $log;

    private $_user_cache = [];

    private $resource;

    public function __construct(Context                                                                                     $context,
                                \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotificationConfig\CollectionFactory $collectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig\CollectionFactory          $repConfigCollectionFactory,
                                \Southbay\CustomCustomer\Model\SoldToRepository                                             $soldToRepository,
                                \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotification\CollectionFactory       $notificationCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory                                  $orderCollectionFactory,
                                \Southbay\CustomCustomer\Model\OrderEntryNotificationFactory                                $factory,
                                UserCollectionFactory                                                                       $userCollectionFactory,
                                ConfigStoreRepository                                                                       $configStoreRepository,
                                SendEmailNotification                                                                       $helperSendEmailNotification,
                                ConfigStoreCollection                                                                       $configStoreCollection,
                                StoreManagerInterface                                                                       $storeManager,
                                \Southbay\Product\Helper\Data                                                               $productHelper,
                                \Magento\Framework\App\ResourceConnection                                                   $resource,
                                LoggerInterface                                                                             $log)
    {
        $this->collectionFactory = $collectionFactory;
        $this->configStoreRepository = $configStoreRepository;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->factory = $factory;
        $this->repConfigCollectionFactory = $repConfigCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->soldToRepository = $soldToRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->helperSendEmailNotification = $helperSendEmailNotification;
        $this->configStoreCollection = $configStoreCollection;
        $this->storeManager = $storeManager;
        $this->productHelper = $productHelper;
        $this->resource = $resource;
        $this->log = $log;
        parent::__construct($context);
    }

    public function sendPendingNotifications()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->notificationCollectionFactory->create();
        $collection->addFieldToFilter(OrderEntryNotificationInterface::ENTITY_SEND_AT, ['lteq' => date('Y-m-d H:i:s')]);
        $collection->addFieldToFilter(OrderEntryNotificationInterface::ENTITY_STATUS, ['eq' => OrderEntryNotificationInterface::STATUS_PENDING]);
        $collection->load();
        $items = $collection->getItems();

        /**
         * @var \Southbay\CustomCustomer\Model\OrderEntryNotification $notification
         */
        foreach ($items as $notification) {
            $this->sendNotification($notification);
        }
    }

    /**
     * @param \Southbay\CustomCustomer\Model\OrderEntryNotification $notification
     * @return void
     */
    public function sendNotification($notification)
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['eq' => $notification->getOrderId()]);
        $collection->addFieldToFilter('status', ['eq' => 'pending']);

        if ($collection->getSize() === 0) {
            $notification->setStatus(OrderEntryNotificationInterface::STATUS_ERROR);
            $notification->save();
            return;
        }

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $collection->getFirstItem();
        $sold_to = $this->findSoldTo($order->getExtCustomerId());

        if (is_null($sold_to)) {
            $notification->setStatus(OrderEntryNotificationInterface::STATUS_ERROR);
            $notification->save();
            return;
        }

        $templateVars = [
            'sold_to_code' => $sold_to['code'],
            'sold_to_name' => $sold_to['name'],
            'increment_id' => $order->getIncrementId()
        ];

        $result = $this->helperSendEmailNotification->sendAnyNotification(
            $notification->getTemplateId(),
            $notification->getEmail(),
            $notification->getName(),
            $templateVars
        );

        $notification->setStatus(OrderEntryNotificationInterface::STATUS_SUCCESS);
        if (isset($result['error'])) {
            $notification->setStatus(OrderEntryNotificationInterface::STATUS_ERROR);

        }
        $notification->save();

        $this->scheduleNotification($order, true);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function scheduleNotification($order, $retry = false)
    {
        $config = $this->findConfig($order->getStoreId());
        if (is_null($config)) {
            return;
        }

        $emails = $this->getApproverEmailsBySoldToId($order->getExtCustomerId());

        foreach ($emails as $email) {
            $this->newNotification($order, $config, $email['to'], $email['name'], $retry);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param OrderEntryNotificationConfigInterface $config
     * @param mixed $emails
     * @param bool $retry
     * @return void
     */
    private function newNotification($order, $config, $email, $name, $retry)
    {
        if ($retry && $config->getRetryAfter() <= 0) {
            return;
        }

        /**
         * @var \Southbay\CustomCustomer\Model\OrderEntryNotification $notification
         */
        $notification = $this->factory->create();
        $notification->setStatus(OrderEntryNotificationInterface::STATUS_PENDING);
        $notification->setEmail($email);
        $notification->setName($name);
        $notification->setCountryCode($config->getCountryCode());
        $notification->setFunctionCode($config->getFunctionCode());
        $notification->setOrderId($order->getId());
        $notification->setIncrementId($order->getIncrementId());
        $notification->setTemplateId($config->getTemplateId());

        if ($retry) {
            $now = new \DateTime();
            $now->modify('+' . $config->getRetryAfter() . ' minutes');
            $notification->setSendAt($now->format('Y-m-d H:i:s'));
        } else {
            $notification->setSendAt(date('Y-m-d H:i:s'));
        }
        $notification->save();
    }

    public function completeAllByOrderId($order_id)
    {
        $this->updatePendingNotificationByOrderId($order_id, OrderEntryNotificationInterface::STATUS_COMPLETE);
    }

    public function cancelAllByOrderId($order_id)
    {
        $this->updatePendingNotificationByOrderId($order_id, OrderEntryNotificationInterface::STATUS_CANCELLED);
    }

    private function updatePendingNotificationByOrderId($order_id, $status)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->notificationCollectionFactory->create();
        $collection->addFieldToFilter(OrderEntryNotificationInterface::ENTITY_ORDER_ID, $order_id);
        $collection->addFieldToFilter(OrderEntryNotificationInterface::ENTITY_STATUS, ['eq' => OrderEntryNotificationInterface::STATUS_PENDING]);
        $collection->load();
        $items = $collection->getItems();

        /**
         * @var \Southbay\CustomCustomer\Model\OrderEntryNotification $item
         */
        foreach ($items as $item) {
            $item->setStatus($status);
            $item->save();
        }
    }

    public function updateNotification($order_id, $status)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->notificationCollectionFactory->create();
        $collection->addFieldToFilter(OrderEntryNotificationInterface::ENTITY_ORDER_ID, $order_id);
        $collection->addFieldToFilter(OrderEntryNotificationInterface::ENTITY_STATUS, ['nin' => [$status, OrderEntryNotificationInterface::STATUS_CANCELLED]]);
        $collection->load();
        $items = $collection->getItems();

        /**
         * @var \Southbay\CustomCustomer\Model\OrderEntryNotification $item
         */
        foreach ($items as $item) {
            $item->setStatus($status);
            $item->save();
        }
    }

    /**
     * @param $store_id
     * @return OrderEntryNotificationConfigInterface|null
     */
    private function findConfig($store_id)
    {
        $config = $this->configStoreRepository->findByStoreId($store_id);

        $country_code = $config->getSouthbayCountryCode();
        $function_code = $config->getSouthbayFunctionCode();

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderEntryNotificationConfigInterface::ENTITY_FUNCTION_CODE, $function_code);
        $collection->addFieldToFilter(OrderEntryNotificationConfigInterface::ENTITY_COUNTRY_CODE, $country_code);

        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        }

        return null;
    }

    private function findSoldTo($sold_to_id)
    {
        $sold_to = $this->soldToRepository->getById($sold_to_id);

        if (is_null($sold_to)) {
            return null;
        }

        return [
            'code' => $sold_to->getCustomerCode(),
            'name' => $sold_to->getCustomerName()
        ];
    }

    private function getApproverEmailsBySoldToId($sold_to_id)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->repConfigCollectionFactory->create();
        $collection->addFieldToFilter(OrderEntryRepConfigInterface::ENTITY_CAN_APPROVE_AT_ONCE, ['eq' => 1]);
        $collection->load();

        $items = $collection->getItems();
        $emails = [];
        $users = [];

        /**
         * @var OrderEntryRepConfigInterface $item
         */
        foreach ($items as $item) {
            $ids = $item->getSoldToIds();
            if (empty($ids)) {
                continue;
            }

            $ids = explode(',', $ids);
            if (in_array($sold_to_id, $ids)) {
                $userCollection = $this->userCollectionFactory->create();
                $userCollection->addFieldToFilter('user_id', $item->getUserCode());
                $userCollection->addFieldToFilter('is_active', 1);
                if ($userCollection->getSize() > 0) {
                    $users[] = $userCollection->getFirstItem();
                }
            }
        }

        /**
         * @var \Magento\User\Model\User $user
         */
        foreach ($users as $user) {
            $emails[] = [
                'to' => $user->getEmail(),
                'name' => $user->getName()
            ];
        }

        return $emails;
    }

    public function IsOrderAtOnce($order)
    {
        $this->loadAtOnceStores();

        if (in_array($order->getStoreId(), $this->storesAtonce)) {
            return true;
        } else return false;
    }

    public function IsOrderAtOnceByOrderId($orderId)
    {
        $this->loadAtOnceStores();

        $storeId = $this->getOrderFieldByOrderId($orderId, 'store_id');

        if (in_array($storeId, $this->storesAtonce)) {
            return true;
        } else return false;
    }

    private function getOrderFieldByOrderId($orderId, $field)
    {
        $connection = $this->resource->getConnection();
        return $connection->fetchOne("SELECT $field FROM sales_order WHERE entity_id = $orderId");
    }

    private function updateOrderByOrderId($orderId, $fields)
    {
        $values = [];
        $status = false;

        foreach ($fields as $key => $value) {
            $values[] = "$key = '$value'";
            if ($key == 'status') {
                $status = $value;
            }
        }

        $values = implode(',', $values);
        $sql = "UPDATE sales_order SET $values WHERE entity_id = $orderId";

        $connection = $this->resource->getConnection();
        $connection->query($sql);

        if ($status) {
            $sql = "UPDATE sales_order_grid SET status = '$status' WHERE entity_id = $orderId";
            $connection->query($sql);
        }
    }

    private function loadAtOnceStores()
    {
        if (is_null($this->storesAtonce)) {
            $this->storesAtonce = [];
            foreach ($this->configStoreCollection as $configStore) {
                if ($configStore->getFunctionCode() == ConfigStoreRepositoryInterface::SOUTHBAY_AT_ONCE) {
                    $storeId = $configStore->getSouthbayStoreCode();
                    $this->storesAtonce[] = $storeId;
                }
            }
        }
    }

    public function approve($adminId, $adminUser, $orderId, $_authorization, $messageManager, $orderEntryRepConfigCollection,
                            $convertOrder, $transaction, $orderRepository, $sourceCommand, $stockResolver)
    {
        $this->log->debug('Start order approval:', ['order_id' => $orderId]);

        $status = $this->getOrderFieldByOrderId($orderId, 'status');

        if ($status != 'pending') {
            $increment_id = $this->getOrderFieldByOrderId($orderId, 'increment_id');
            $messageManager->addErrorMessage(__('La orden #%1 no esta en el estado esperado', $increment_id));
            return false;
        }

        $allow = false;
        if ($this->IsOrderAtOnceByOrderId($orderId)) {
            if ($_authorization->isAllowed('Southbay_ApproveOrders::at_once')) {
                $allow = true;
            } else {
                if (!isset($this->_source_code_cache[$adminId])) {
                    if (!$orderEntryRepConfigCollection->isLoaded()) {
                        $orderEntryRepConfigCollection->addFieldToFilter('magento_user_code', $adminId);
                    }
                    $filteredItems = $orderEntryRepConfigCollection->getFirstItem()->getSouthbayCustomerConfigSoldToIds();
                    $this->_source_code_cache[$adminId] = $filteredItems;
                }

                $filteredItems = $this->_source_code_cache[$adminId];

                if (empty($filteredItems)) {
                    $messageManager->addErrorMessage(__('No tiene permisos asignados a este usuario.'));
                    return false;
                }

                $orderSoldTo = $this->getOrderFieldByOrderId($orderId, 'ext_customer_id');
                $soldToAvailable = explode(',', $filteredItems);

                if (!in_array($orderSoldTo, $soldToAvailable)) {
                    $messageManager->addErrorMessage(__('No tiene permisos para aprobar esta orden.'));
                    return false;
                }

                $allow = true;
            }
        } else if ($_authorization->isAllowed('Southbay_ApproveOrders::future')) {
            $allow = true;
        }

        if (!$allow) {
            $messageManager->addErrorMessage(__('No tiene permisos para aprobar esta orden'));
            return false;
        }

        // $order->setIsInProcess(true);
        // $order->save();

        $this->updateOrderByOrderId($orderId, [
            'status' => 'processing',
            'state' => 'processing'
        ]);

        $this->completeAllByOrderId($orderId);

        return true;
    }

    private function getSourceCode($orderShipment, $sourceCommand, $stockResolver, $messageManager)
    {
        $order = $orderShipment->getOrder();
        try {
            if (!isset($this->_source_code_cache[$order->getStoreId()])) {
                $this->_source_code_cache[$order->getStoreId()] = $this->productHelper->getSourceCodeByStoreCode($order->getStore()->getCode());
            }

            $source_code = $this->_source_code_cache[$order->getStoreId()];

            // $this->log->debug('getSourceCode', ['store_id' => $order->getStoreId(), 'source_code' => $source_code]);

            $orderShipment->getExtensionAttributes()->setSourceCode($source_code);
        } catch (\Exception $e) {
            $this->log->error('Error approving sale order,no source or stock assigned to website', ['e' => $e]);
            $messageManager->addWarningMessage(__('no source or stock assigned to website'));
        }
    }
}
