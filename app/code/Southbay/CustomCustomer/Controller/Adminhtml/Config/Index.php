<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
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
        $resultPage->setActiveMenu('Southbay_CustomCustomer::CustomerConfig');
        $resultPage->addBreadcrumb(__('Configuración de clientes'), __('Clientes'));
        $resultPage->getConfig()->getTitle()->prepend(__('Configuración de clientes'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}

