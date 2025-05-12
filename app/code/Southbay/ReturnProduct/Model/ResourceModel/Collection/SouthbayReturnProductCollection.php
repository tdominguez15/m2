<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnProductCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReturnProduct',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProduct'
        );
    }
}
