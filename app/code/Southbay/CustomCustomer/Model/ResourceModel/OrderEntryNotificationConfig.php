<?php

namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\OrderEntryNotificationConfigInterface;

class OrderEntryNotificationConfig extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(OrderEntryNotificationConfigInterface::TABLE, OrderEntryNotificationConfigInterface::ENTITY_ID);
    }
}
