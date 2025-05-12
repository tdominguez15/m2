<?php

namespace Southbay\ApproveOrders\Controller\Adminhtml\Order;

use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Inventory\Model\Source\Command\GetSourcesAssignedToStockOrderedByPriority;
use Magento\InventorySales\Model\StockResolver;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;
use Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig\Collection as OrderEntryRepConfigCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassApprove extends \Magento\Backend\App\Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var ShipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * @var AdminSession
     */
    protected $adminSession;

    /**
     * @var ConvertOrder
     */
    private $convertOrder;

    /**
     * @var LoggerInterface
     */
    private $log;

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
     * @var Filter
     */
    private $filter;
    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    private $request;

    /**
     * MassApprove constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentNotifier $shipmentNotifier
     * @param ConvertOrder $convertOrder
     * @param AdminSession $adminSession
     * @param LoggerInterface $log
     * @param OrderEntryRepConfigCollection $orderEntryRepConfigCollection
     * @param SouthbayApproveOrderHelper $approveOrderHelper
     * @param Filter $filter
     * @param CollectionFactory $orderCollectionFactory
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
        AdminSession                               $adminSession,
        LoggerInterface                            $log,
        OrderEntryRepConfigCollection              $orderEntryRepConfigCollection,
        SouthbayApproveOrderHelper                 $approveOrderHelper,
        GetSourcesAssignedToStockOrderedByPriority $sourceCommand,
        StockResolver                              $stockResolver,
        Filter                                     $filter,
        CollectionFactory                          $orderCollectionFactory
    )
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->adminSession = $adminSession;
        $this->convertOrder = $convertOrder;
        $this->log = $log;
        $this->orderEntryRepConfigCollection = $orderEntryRepConfigCollection;
        $this->approveOrderHelper = $approveOrderHelper;
        $this->sourceCommand = $sourceCommand;
        $this->stockResolver = $stockResolver;
        $this->filter = $filter;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->request = $context->getRequest();
        $this->_authorization = $context->getAuthorization();
        parent::__construct($context);
    }

    /**
     * Execute the mass approve action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $selectedIds = $this->request->getParam('selected');

        if (empty($selectedIds) || !is_array($selectedIds)) {
            $ordersId = $this->filter->getCollection($this->orderCollectionFactory->create())->getAllIds();
        } else {
            $ordersId = $this->filter->getCollection($this->orderCollectionFactory->create())->addFieldToFilter('entity_id', ['in' => $selectedIds])->getAllIds();
        }

        $adminUser = $this->adminSession->getUser()->getName();
        $adminId = $this->adminSession->getUser()->getId();

        /*
        $filteredCollection = $this->orderEntryRepConfigCollection
            ->addFieldToFilter('magento_user_code', $adminId);
        $filteredItems = $filteredCollection->getFirstItem()->getSouthbayCustomerConfigSoldToIds();

        if (empty($filteredItems)) {
            $filteredItems = '';
            $ordersId = [];
            $this->messageManager->addErrorMessage(__('No tiene permisos para autorizar las ordenes seleccionadas'));
        }

        $soldToAvailable = explode(',', $filteredItems);
        */

        $any_error = false;

        foreach ($ordersId as $orderId) {
            try {
                $_result = $this->approveOrderHelper->approve($adminId, $adminUser, $orderId,
                    $this->_authorization,
                    $this->messageManager,
                    $this->orderEntryRepConfigCollection,
                    $this->convertOrder,
                    $this->transaction,
                    $this->orderRepository,
                    $this->sourceCommand,
                    $this->stockResolver);

                if (!$_result) {
                    $any_error = true;
                }
            } catch (LocalizedException $e) {
                $this->log->error('Localized error approved sale order', ['e' => $e]);
                $this->messageManager->addErrorMessage($e->getMessage());
                $any_error = true;
            } catch (\Exception $e) {
                $this->log->error('Error approved sale order', ['e' => $e, 't' => $e->getTrace()]);
                $this->messageManager->addErrorMessage(__('An error occurred while approving the order.'));
                $any_error = true;
            }
        }

        if (!$any_error) {
            $this->messageManager->addSuccessMessage(__('Las ordenes fueron aprobadas'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/index');
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
