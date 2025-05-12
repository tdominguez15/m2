<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Reception;

use Magento\Framework\View\Result\PageFactory;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    private $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context               $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory                                       $resultPageFactory
    )
    {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Nueva Recepción'));
        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
