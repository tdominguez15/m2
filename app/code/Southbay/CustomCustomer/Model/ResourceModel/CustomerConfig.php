<?php

namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\CustomerConfigInterface;

class CustomerConfig extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(CustomerConfigInterface::TABLE, CustomerConfigInterface::ENTITY_ID);
    }
}
