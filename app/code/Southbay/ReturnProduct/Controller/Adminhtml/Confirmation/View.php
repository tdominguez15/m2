<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Confirmation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $resultRawFactory;
    private $layoutFactory;

    public function __construct(
        Context                                         $context,
        PageFactory                                     $resultPageFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory           $layoutFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $layout = $this->layoutFactory->create();

        $block = $layout
            ->createBlock('\Southbay\ReturnProduct\Block\Adminhtml\MyReturn')
            ->setTemplate('Southbay_ReturnProduct::my_return.phtml')
            ->toHtml();

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($block);

        return $resultRaw;
    }

    public function _isAllowed()
    {
        return true;
    }
}
