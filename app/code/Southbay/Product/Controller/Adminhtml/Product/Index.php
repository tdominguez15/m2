<?php

namespace Southbay\Product\Controller\Adminhtml\Product;

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
        $resultPage->setActiveMenu('Southbay_Product::manager');
        $resultPage->addBreadcrumb(__('Lineas'), __('Order Entry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Lineas'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
