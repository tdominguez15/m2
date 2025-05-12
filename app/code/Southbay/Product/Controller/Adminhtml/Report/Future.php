<?php

namespace Southbay\Product\Controller\Adminhtml\Report;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Future extends Action
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
        $resultPage->setActiveMenu('Southbay_Product::OrderReportFuture');
        $resultPage->addBreadcrumb(__('Reporte de Ordenes Futuras'), __('Order Entry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Reporte de Ordenes Futuras'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
