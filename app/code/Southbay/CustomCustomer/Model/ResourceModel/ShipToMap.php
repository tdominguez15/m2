<?php

namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\ShipToMapInterface;

class ShipToMap extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(ShipToMapInterface::TABLE, ShipToMapInterface::ENTITY_ID);
    }
}
