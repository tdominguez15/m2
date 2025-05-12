<?php

namespace Southbay\ApproveOrders\Cron;

use Psr\Log\LoggerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\Order;
use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\Collection as ConfigStoreCollection;
use Magento\Store\Model\StoreManagerInterface;

class CancelOrderAtonce
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var ConfigStoreCollection
     */
    protected $configStoreCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CancelOrderAtonce constructor.
     *
     * @param LoggerInterface $logger
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param HistoryFactory $historyFactory
     * @param ConfigStoreCollection $configStoreCollection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        LoggerInterface $logger,
        OrderCollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HistoryFactory $historyFactory,
        ConfigStoreCollection $configStoreCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->historyFactory = $historyFactory;
        $this->configStoreCollection = $configStoreCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute the cron job to cancel all pending orders.
     *
     * @throws \Exception
     */
    public function execute(): void
    {
        $stores = $this->getAtonceStores();
        if (empty($stores)) {
            $this->logger->error('Error canceling order: configuracion de tiendas no encontradas.');
            throw new \Exception('configuracion de tiendas no encontradas.');
        }

        try {
            $orderCollection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', 'pending')
                ->addFieldToFilter('store_id', ['in' => $stores]);

            foreach ($orderCollection as $order) {
                $this->cancelOrder($order);
            }

            $this->logger->info('Successfully canceled pending orders.');
        } catch (\Exception $e) {
            $this->logger->error('Error canceling pending orders: ' . $e->getMessage());
        }
    }

    /**
     * Cancela una orden específica
     *
     * @param Order $order
     * @param int $websiteId
     * @return void
     */
    protected function cancelOrder(Order $order): void
    {
        try {
            if (!$order->canCancel()) {
                throw new \Exception('Order cannot be canceled.');
            }

            // Añadir historial de estado
            $history = $this->historyFactory->create()
                ->setStatus(Order::STATE_CANCELED)
                ->setComment('Order automatically canceled by cron job')
                ->setEntityName(Order::ENTITY)
                ->setIsCustomerNotified(false);
            $order->addStatusHistory($history);

            // Cancelar la orden
            $order->cancel();
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->logger->error('Error canceling order ' . $order->getIncrementId() . ': ' . $e->getMessage());
        }
    }

    /**
     * Get Atonce Stores.
     *
     * @return array
     */
    protected function getAtonceStores(): array
    {
        $storesAtonce = [];
        foreach ($this->configStoreCollection as $configStore) {
            if ($configStore->getFunctionCode() == ConfigStoreRepositoryInterface::SOUTHBAY_AT_ONCE) {
                $storeId = $configStore->getSouthbayStoreCode();
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
                $storesAtonce[$storeId] = $websiteId;
            }
        }
        return $storesAtonce;
    }
}
