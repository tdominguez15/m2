<?php

namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\OrderEntryRepConfigInterface;

class OrderEntryRepConfig extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(OrderEntryRepConfigInterface::TABLE, OrderEntryRepConfigInterface::ENTITY_ID);
    }
}

