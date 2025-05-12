<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\SouthbayProductImportHistory', 'Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory');
    }
}
