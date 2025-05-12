<?php

namespace Southbay\Product\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayProductsUpdateCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\Product\Model\SouthbayProductsUpdate',
            'Southbay\Product\Model\ResourceModel\SouthbayProductsUpdate'
        );
    }
}
