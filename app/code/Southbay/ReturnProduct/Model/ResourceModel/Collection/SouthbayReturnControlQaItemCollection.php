<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnControlQaItemCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReturnControlQaItem',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQaItem'
        );
    }
}
