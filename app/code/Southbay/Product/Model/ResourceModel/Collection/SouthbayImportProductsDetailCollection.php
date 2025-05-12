<?php

namespace Southbay\Product\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayImportProductsDetailCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\Product\Model\SouthbayImportProductsDetail',
            'Southbay\Product\Model\ResourceModel\SouthbayImportProductsDetail'
        );
    }
}
