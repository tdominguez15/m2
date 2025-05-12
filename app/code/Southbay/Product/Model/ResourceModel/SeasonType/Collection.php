<?php

namespace Southbay\Product\Model\ResourceModel\SeasonType;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\SeasonType', 'Southbay\Product\Model\ResourceModel\SeasonType');
    }
}
