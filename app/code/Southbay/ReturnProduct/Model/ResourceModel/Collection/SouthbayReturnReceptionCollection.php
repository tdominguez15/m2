<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnReceptionCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReturnReception',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnReception'
        );
    }
}
