<?php

namespace Southbay\Product\Controller\Adminhtml\ReportAttribute;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AtOnce extends Action
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
        $resultPage->setActiveMenu('Southbay_Product::OrderReportAttributeAtOnce');
        $resultPage->addBreadcrumb(__('Reporte de Control de Productos At Once'), __('Order Entry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Reporte de Control de Productos At Once'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
