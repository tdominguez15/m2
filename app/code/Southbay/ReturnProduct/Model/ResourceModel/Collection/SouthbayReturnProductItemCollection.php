<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnProductItemCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReturnProductItem',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItem'
        );
    }
}
