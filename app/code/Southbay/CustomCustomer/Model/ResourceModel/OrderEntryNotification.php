<?php

namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\OrderEntryNotificationInterface;

class OrderEntryNotification extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(OrderEntryNotificationInterface::TABLE, OrderEntryNotificationInterface::ENTITY_ID);
    }
}
