<?php
namespace Southbay\ApproveOrders\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig\Collection as OrderEntryRepConfigCollection;
use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;


class MassCancel extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Sales::cancel';

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderEntryRepConfigCollection
     */
    private $orderEntryRepConfigCollection;

    /**
     * @var SouthbayApproveOrderHelper
     */
    private $approveOrderHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderEntryRepConfigCollection $orderEntryRepConfigCollection
     * @param SouthbayApproveOrderHelper $approveOrderHelper
     * @param ManagerInterface $messageManager
     * @param AuthorizationInterface $authorization
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        OrderEntryRepConfigCollection $orderEntryRepConfigCollection,
        SouthbayApproveOrderHelper $approveOrderHelper,
        ManagerInterface $messageManager,
        AuthorizationInterface $authorization,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->orderRepository = $orderRepository;
        $this->orderEntryRepConfigCollection = $orderEntryRepConfigCollection;
        $this->approveOrderHelper = $approveOrderHelper;
        $this->messageManager = $messageManager;
        $this->authorization = $authorization;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        $adminName = $this->_auth->getUser()->getName();

        foreach ($collection->getItems() as $order) {
            try {
                if ($this->canCancelOrder($order)) {
                    $orderId = $order->getEntityId();
                    $this->orderManagement->cancel($orderId);
                    $order = $this->orderRepository->get($orderId);
                    $order->addStatusHistoryComment(__('La orden ha sido cancelada por el administrador: %1', $adminName));
                    $this->orderRepository->save($order);

                    $countCancelOrder++;
                } else {
                    $countNonCancelOrder++;
                }
            } catch (\Exception $e) {
                $countNonCancelOrder++;
                $this->messageManager->addErrorMessage(__('Error cancelando la orden con ID %1: %2', $order->getEntityId(), $e->getMessage()));
            }
        }

        if ($countNonCancelOrder) {
            $this->messageManager->addErrorMessage(__('%1 order(s) cannot be canceled.', $countNonCancelOrder));
        }

        if ($countCancelOrder) {
            $this->messageManager->addSuccessMessage(__('We canceled %1 order(s).', $countCancelOrder));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    /**
     * Verifica si la orden puede ser cancelada.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    private function canCancelOrder($order)
    {
        $orderId = $order->getEntityId();
        $adminId = $this->_auth->getUser()->getId();
        $allow = false;

        try {
            if ($this->approveOrderHelper->isOrderAtOnce($order)) {
                if ($this->authorization->isAllowed('Southbay_ApproveOrders::at_once')) {
                    $allow = true;
                } else {
                    $filteredCollection = $this->orderEntryRepConfigCollection->addFieldToFilter('magento_user_code', $adminId);
                    $filteredItems = $filteredCollection->getFirstItem()->getSouthbayCustomerConfigSoldToIds();

                    if (empty($filteredItems)) {
                        $this->messageManager->addErrorMessage(__('No tiene permisos asignados a este usuario.'));
                        return false;
                    }

                    $orderSoldTo = $order->getExtCustomerId();
                    $soldToAvailable = explode(',', $filteredItems);

                    if (!in_array($orderSoldTo, $soldToAvailable)) {
                        $this->messageManager->addErrorMessage(__('No tiene permisos para cancelar esta orden.'));
                        return false;
                    }

                    $allow = true;
                }
            } else if ($this->authorization->isAllowed('Southbay_ApproveOrders::future')) {
                $allow = true;
            }

            if (!$allow) {
                $this->messageManager->addErrorMessage(__('No tiene permisos para cancelar esta orden.'));
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error al validar la cancelación: ' . $e->getMessage()));
            return false;
        }
    }
}
