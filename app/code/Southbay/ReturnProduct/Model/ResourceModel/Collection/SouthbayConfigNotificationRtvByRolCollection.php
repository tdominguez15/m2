<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayConfigNotificationRtvByRolCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayConfigNotificationRtvByRol',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtvByRol'
        );
    }
}
