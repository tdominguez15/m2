<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ReasonReject;

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
        $resultPage->setActiveMenu('Southbay_ReturnProduct::ReasonReject');
        $resultPage->addBreadcrumb(__('Motivos de devolución'), __('Devoluciones'));
        $resultPage->getConfig()->getTitle()->prepend(__('Motivos de devolución'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
