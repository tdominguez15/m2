<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\ShipToMap;

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
        $resultPage->setActiveMenu('Southbay_CustomCustomer::ShipToMap');
        $resultPage->addBreadcrumb(__('Destino Mercaderia - Mapeo Codigos'), __('Destino Mercaderia'));
        $resultPage->getConfig()->getTitle()->prepend(__('Destino Mercaderia - Mapeo Codigos'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}

