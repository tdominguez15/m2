<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\ControlQa;

use Magento\Backend\Block\Template;

class ControlQaBlock extends Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array                                   $data = []
    )
    {
        parent::__construct($context, $data);
    }
}
