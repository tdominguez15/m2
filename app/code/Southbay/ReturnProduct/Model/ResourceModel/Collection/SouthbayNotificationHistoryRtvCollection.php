<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayNotificationHistoryRtvCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayNotificationHistoryRtv',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayNotificationHistoryRtv'
        );
    }
}
