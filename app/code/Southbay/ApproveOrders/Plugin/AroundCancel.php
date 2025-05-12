<?php

namespace Southbay\ApproveOrders\Plugin;

use Magento\Sales\Controller\Adminhtml\Order\Cancel;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig\Collection as OrderEntryRepConfigCollection;
use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;

class AroundCancel
{
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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * AroundCancel constructor.
     *
     * @param AdminSession $adminSession
     * @param OrderEntryRepConfigCollection $orderEntryRepConfigCollection
     * @param SouthbayApproveOrderHelper $approveOrderHelper
     * @param ManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param AuthorizationInterface $authorization
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        AdminSession $adminSession,
        OrderEntryRepConfigCollection $orderEntryRepConfigCollection,
        SouthbayApproveOrderHelper $approveOrderHelper,
        ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        AuthorizationInterface $authorization,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->adminSession = $adminSession;
        $this->orderEntryRepConfigCollection = $orderEntryRepConfigCollection;
        $this->approveOrderHelper = $approveOrderHelper;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->authorization = $authorization;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param Cancel $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Redirect|void
     */
    public function aroundExecute(Cancel $subject, \Closure $proceed)
    {
        $orderId = $subject->getRequest()->getParam('order_id');
        $adminId = $this->adminSession->getUser()->getId();

        try {
            $order = $this->orderRepository->get($orderId);
            $allow = false;

            if ($this->approveOrderHelper->isOrderAtOnce($order)) {
                if ($this->authorization->isAllowed('Southbay_ApproveOrders::at_once')) {
                    $allow = true;
                } else {
                    $filteredCollection = $this->orderEntryRepConfigCollection->addFieldToFilter('magento_user_code', $adminId);

                    $filteredItems = $filteredCollection->getFirstItem()->getSouthbayCustomerConfigSoldToIds();
                    if (empty($filteredItems)) {
                        $this->messageManager->addErrorMessage(__('No tiene permisos asignados a este usuario.'));
                        return $this->redirectToOrderPage($orderId);
                    }
                    $orderSoldTo = $order->getExtCustomerId();
                    $soldToAvailable = explode(',', $filteredItems);

                    if (!in_array($orderSoldTo, $soldToAvailable)) {
                        $this->messageManager->addErrorMessage(__('No tiene permisos para cancelar esta orden.'));
                        return $this->redirectToOrderPage($orderId);
                    }

                    $allow = true;
                }
            } else if ($this->authorization->isAllowed('Southbay_ApproveOrders::future')) {
                $allow = true;
            }

            if (!$allow) {
                $this->messageManager->addErrorMessage(__('No tiene permisos para cancelar esta orden.'));
                return $this->redirectToOrderPage($orderId);
            }
            $order->addStatusHistoryComment(__('La orden ha sido cancelada por el administrador: %1', $this->adminSession->getUser()->getName()));
            $this->orderRepository->save($order);
            return $proceed();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error al validar la cancelación: ' . $e->getMessage()));
            return $this->redirectToOrderPage($orderId);
        }
    }

    /**
     * Redirect to the order page with an error message.
     *
     * @param int $orderId
     * @return Redirect
     */
    private function redirectToOrderPage(int $orderId): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }
}
