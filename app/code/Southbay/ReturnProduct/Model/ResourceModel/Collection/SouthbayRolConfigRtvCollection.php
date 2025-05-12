<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayRolConfigRtvCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayRolConfigRtv',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayRolConfigRtv'
        );
    }
}
