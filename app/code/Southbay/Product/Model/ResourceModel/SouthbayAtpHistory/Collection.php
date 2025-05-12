<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayAtpHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\SouthbayAtpHistory', 'Southbay\Product\Model\ResourceModel\SouthbayAtpHistory');
    }
}
