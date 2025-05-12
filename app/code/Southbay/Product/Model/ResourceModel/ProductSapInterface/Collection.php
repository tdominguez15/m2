<?php

namespace Southbay\Product\Model\ResourceModel\ProductSapInterface;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ProductSapInterface', 'Southbay\Product\Model\ResourceModel\ProductSapInterface');
    }
}
