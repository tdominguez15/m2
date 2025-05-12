<?php
namespace Southbay\ReturnProduct\Block\Adminhtml\Confirmation;

use Southbay\ReturnProduct\Block\Adminhtml\PageGridBaseBlock;

class Grid extends PageGridBaseBlock {
    public function __construct(\Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    protected function getButtonsProps()
    {
        return null;
    }

    protected function getGridBlockType()
    {
        return 'Southbay\ReturnProduct\Block\Adminhtml\Confirmation\Grid\Grid';
    }
}
