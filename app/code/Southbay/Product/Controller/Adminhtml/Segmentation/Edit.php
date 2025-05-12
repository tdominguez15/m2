<?php

namespace Southbay\Product\Controller\Adminhtml\Segmentation;

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
        $resultPage->setActiveMenu('Southbay_Product::ProductSegmentation');
        $resultPage->addBreadcrumb(__('Editar Segmentacion'), __('Order Entry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Editar Segmentacion'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
