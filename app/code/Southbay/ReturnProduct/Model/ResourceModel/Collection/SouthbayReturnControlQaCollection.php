<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnControlQaCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReturnControlQa',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQa'
        );
    }
}
