<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn as EntityInterface;

class SouthbayExchangeReturn extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EntityInterface::TABLE, EntityInterface::ENTITY_ID);
    }
}
