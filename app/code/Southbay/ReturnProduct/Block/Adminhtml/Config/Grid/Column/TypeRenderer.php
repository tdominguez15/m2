<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Grid\Column;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnConfig;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class TypeRenderer extends AbstractRenderer
{
    private $log;

    public function __construct(Context                         $context,
                                array                           $data = [])
    {
        $this->log = $context->getLogger();
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $type = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_TYPE);

        return __(SouthbayReturnConfig::getTypeName($type));
    }
}
