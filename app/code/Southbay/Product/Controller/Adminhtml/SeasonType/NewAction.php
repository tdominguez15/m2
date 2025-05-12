<?php

namespace Southbay\Product\Controller\Adminhtml\SeasonType;

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
        $resultPage->setActiveMenu('Southbay_Product::SeasonType');
        $resultPage->addBreadcrumb(__('Nuevo Tipo de temporada'), __('Order Entry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Nuevo Tipo de temporada'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
