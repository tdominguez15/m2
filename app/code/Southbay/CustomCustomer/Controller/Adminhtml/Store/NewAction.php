<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\Store;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends Action
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
        $resultPage->setActiveMenu('Southbay_CustomCustomer::StoreConfig');
        $resultPage->addBreadcrumb(__('Nueva configuración de tienda'), __('Clientes'));
        $resultPage->getConfig()->getTitle()->prepend(__('Nueva configuración de tienda'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
