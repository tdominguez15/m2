<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnBalanceItemCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\SouthbayReturnBalanceItem', 'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnBalanceItem');
    }
}
