<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Reception;

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
        $resultPage->setActiveMenu('Southbay_ReturnProduct::Reception');
        $resultPage->addBreadcrumb(__('Recepción de devoluciones'), __('Devoluciones'));
        $resultPage->getConfig()->getTitle()->prepend(__('Recepción de devoluciones'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
