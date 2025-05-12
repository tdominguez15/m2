<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\Product\Api\Data\SouthbayProductGroupInterface;

class SouthbayProductGroup extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SouthbayProductGroupInterface::TABLE, SouthbayProductGroupInterface::ENTITY_ID);
    }
}
