<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class Grid extends Action
{
    /**
     * @var Rawfactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param Context       $context
     * @param Rawfactory    $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        Rawfactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\RawFactory
     */
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        try {

            $html = $this->layoutFactory->create()->createBlock(
                'Southbay\ReturnProduct\Block\Adminhtml\Config\Grid\Grid',
                'grid.view.grid'
            )->toHtml();
        } catch(\Exception $e ) {
            throw $e;
        }

        return $resultRaw->setContents($html);
    }
}
