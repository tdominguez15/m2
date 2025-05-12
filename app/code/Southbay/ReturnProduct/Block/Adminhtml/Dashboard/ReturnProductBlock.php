<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Dashboard;

use Magento\Backend\Block\Template;

class ReturnProductBlock extends Template
{
    private $helper;

    public function __construct(
        \Southbay\ReturnProduct\Helper\Data     $helper,
        \Magento\Backend\Block\Template\Context $context,
        array                                   $data = []
    )
    {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    public function showDownloadDetail()
    {
        // return !empty($this->helper->getTypeReturnByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK));
        return true;
    }
}
