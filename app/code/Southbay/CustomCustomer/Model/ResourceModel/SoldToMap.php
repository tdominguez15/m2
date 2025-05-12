<?php

namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\SoldToMapInterface;

class SoldToMap extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(SoldToMapInterface::TABLE, SoldToMapInterface::ENTITY_ID);
    }
}
