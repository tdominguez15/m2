<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayConfigNotificationRtvCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayConfigNotificationRtv',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtv'
        );
    }
}
