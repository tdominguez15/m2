<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Southbay\CustomCustomer\Block\Season;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Southbay\CustomCustomer\Model\ShipToRepository;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    //protected $_template = 'Magento_Sales::order/history.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    private $shipToRepository;
    private $ship_to_cache = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context           $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session                            $customerSession,
        ShipToRepository                                           $shipToRepository,
        \Magento\Sales\Model\Order\Config                          $orderConfig,
        array                                                      $data = []
    )
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->shipToRepository = $shipToRepository;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Season Orders'));
    }

    /**
     * Provide order collection factory
     *
     * @return CollectionFactoryInterface
     * @deprecated 100.1.1
     */
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */

    public function getOrders()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $date = new \DateTime();
        $date->modify('-90 days');
        $formattedDate = $date->format('Y-m-d H:i:s');
        if (!$this->orders) {
            $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->addFieldToFilter('created_at', ['gteq' => $formattedDate]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->orders;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.order.history.pager'
            )->setCollection(
                $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get order view URL
     *
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * Get order track URL
     *
     * @param object $order
     * @return string
     * @deprecated 102.0.3 Action does not exist
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getTrackUrl($order)
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);
        return '';
    }

    /**
     * Get reorder URL
     *
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * Get customer account URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Get message for no orders.
     *
     * @return \Magento\Framework\Phrase
     * @since 102.1.0
     */
    public function getEmptyOrdersMessage()
    {
        return __('You have placed no orders.');
    }

    public function getShipToName($id)
    {
        if (!isset($this->ship_to_cache[$id])) {
            $shipTo = $this->shipToRepository->getById($id);
            $this->ship_to_cache[$id] = $shipTo->getName();
        }

        return $this->ship_to_cache[$id];
    }
}
