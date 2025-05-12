<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductGroup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\SouthbayProductGroup', 'Southbay\Product\Model\ResourceModel\SouthbayProductGroup');
    }
}
