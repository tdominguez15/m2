<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ConfigApproval;

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
        $resultPage->setActiveMenu('Southbay_ReturnProduct::ConfigApproval');
        $resultPage->addBreadcrumb(__('Configuración de aprobadores'), __('Devoluciones'));
        $resultPage->getConfig()->getTitle()->prepend(__('Configuración de aprobadores'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
