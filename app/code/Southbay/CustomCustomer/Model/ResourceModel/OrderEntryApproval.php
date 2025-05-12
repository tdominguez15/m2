<?php

namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\OrderEntryApprovalsInterface;

class OrderEntryApproval extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(OrderEntryApprovalsInterface::TABLE, OrderEntryApprovalsInterface::ENTITY_ID);
    }
}
