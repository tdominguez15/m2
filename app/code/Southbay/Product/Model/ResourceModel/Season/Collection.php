<?php

namespace Southbay\Product\Model\ResourceModel\Season;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\Season', 'Southbay\Product\Model\ResourceModel\Season');
    }
}
