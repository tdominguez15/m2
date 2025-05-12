<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\ShipToMap;

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
        $resultPage->setActiveMenu('Southbay_CustomCustomer::ShipToMap');
        $resultPage->addBreadcrumb(__('Nuevo Mapeo Codigo'), __('Destino Mercaderia'));
        $resultPage->getConfig()->getTitle()->prepend(__('Nuevo Mapeo Codigo'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
