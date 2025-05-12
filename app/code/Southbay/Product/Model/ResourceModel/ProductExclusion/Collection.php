<?php

namespace Southbay\Product\Model\ResourceModel\ProductExclusion;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ProductExclusion', 'Southbay\Product\Model\ResourceModel\ProductExclusion');
    }
}
