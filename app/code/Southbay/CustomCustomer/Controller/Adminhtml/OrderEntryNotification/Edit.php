<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\OrderEntryNotification;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Southbay_CustomCustomer::OrderEntryNotification');
        $resultPage->addBreadcrumb(__('Editar configuración'), __('Order Entry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Editar configuración'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
