<?php

namespace Southbay\ApproveOrders\Controller\Adminhtml\Order;

use Exception;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig\Collection as OrderEntryRepConfigCollection;
use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;
use Magento\Inventory\Model\Source\Command\GetSourcesAssignedToStockOrderedByPriority;
use Magento\InventorySales\Model\StockResolver;

class Approve extends \Magento\Backend\App\Action
{

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var ShipmentNotifier
     */
    private $shipmentNotifier;

    /**
     * @var ConvertOrder
     */
    private $convertOrder;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var AdminSession
     */
    private $adminSession;

    /**
     * @var OrderEntryRepConfigCollection
     */
    private $orderEntryRepConfigCollection;

    /**
     * @var SouthbayApproveOrderHelper
     */
    private $approveOrderHelper;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriority
     */
    private $sourceCommand;

    /**
     * @var StockResolver
     */
    private $stockResolver;

    /**
     * Approve constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentNotifier $shipmentNotifier
     * @param ConvertOrder $convertOrder
     * @param LoggerInterface $log
     * @param AdminSession $adminSession
     * @param OrderEntryRepConfigCollection $orderEntryRepConfigCollection
     * @param SouthbayApproveOrderHelper $approveOrderHelper
     * @param GetSourcesAssignedToStockOrderedByPriority $sourceCommand
     * @param StockResolver $stockResolver
     */
    public function __construct(
        \Magento\Backend\App\Action\Context        $context,
        OrderRepositoryInterface                   $orderRepository,
        InvoiceService                             $invoiceService,
        InvoiceSender                              $invoiceSender,
        Transaction                                $transaction,
        ShipmentFactory                            $shipmentFactory,
        ShipmentNotifier                           $shipmentNotifier,
        ConvertOrder                               $convertOrder,
        LoggerInterface                            $log,
        AdminSession                               $adminSession,
        OrderEntryRepConfigCollection              $orderEntryRepConfigCollection,
        SouthbayApproveOrderHelper                 $approveOrderHelper,
        GetSourcesAssignedToStockOrderedByPriority $sourceCommand,
        StockResolver                              $stockResolver
    )
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->convertOrder = $convertOrder;
        $this->log = $log;
        $this->adminSession = $adminSession;
        $this->orderEntryRepConfigCollection = $orderEntryRepConfigCollection;
        $this->approveOrderHelper = $approveOrderHelper;
        $this->sourceCommand = $sourceCommand;
        $this->stockResolver = $stockResolver;
        parent::__construct($context);
    }

    /**
     * Execute the approve action for a single order
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $adminUser = $this->adminSession->getUser()->getName();
        $adminId = $this->adminSession->getUser()->getId();

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->approveOrderHelper->approve($adminId, $adminUser, $orderId,
                $this->_authorization,
                $this->messageManager,
                $this->orderEntryRepConfigCollection,
                $this->convertOrder,
                $this->transaction,
                $this->orderRepository,
                $this->sourceCommand,
                $this->stockResolver);

        } catch (LocalizedException $e) {
            $this->log->error('Localized error approving sale order', ['e' => $e]);
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->log->error('Error approving sale order', ['e' => $e]);
            $this->messageManager->addErrorMessage(__('An error occurred while approving the order.'));
        }

        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Check if the user is allowed to execute this action
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::actions_edit');
    }
}
