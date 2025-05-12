<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReasonRejectCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReasonReject',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReasonReject'
        );
    }
}
