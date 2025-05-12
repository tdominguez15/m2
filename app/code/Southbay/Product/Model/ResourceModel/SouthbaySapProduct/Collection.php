<?php

namespace Southbay\Product\Model\ResourceModel\SouthbaySapProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\SouthbaySapProduct', 'Southbay\Product\Model\ResourceModel\SouthbaySapProduct');
    }
}
