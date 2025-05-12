<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductChangesHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\SouthbayProductChangesHistory', 'Southbay\Product\Model\ResourceModel\SouthbayProductChangesHistory');
    }
}
