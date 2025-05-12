<?php

namespace Southbay\Issues\Controller\Adminhtml\UiTest;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $session;

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
        $this->session = $context->getSession();
    }

    public function execute()
    {
        $run_model_id = $this->session->getRunModelId();

        if ($run_model_id) {
            $this->session->unsRunModelId();
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/edit', ['id' => $run_model_id]);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Southbay_Issues::ui_test');
        $resultPage->addBreadcrumb(__('Pruebas manuales'), __('Issues'));
        $resultPage->getConfig()->getTitle()->prepend(__('Issues'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
