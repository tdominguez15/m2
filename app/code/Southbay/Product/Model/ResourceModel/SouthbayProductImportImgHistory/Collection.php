<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\SouthbayProductImportImgHistory', 'Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory');
    }
}
