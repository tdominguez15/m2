<?php

namespace Southbay\Product\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class BagsSizeExtensionOption extends AbstractSource
{
    protected $optionFactory;

    public function getAllOptions()
    {
        $this->_options = [];
        $this->_options[] = ['label' => 'Label 1', 'value' => 'value 1'];
        $this->_options[] = ['label' => 'Label 2', 'value' => 'value 2'];
        return $this->_options;
    }
}
