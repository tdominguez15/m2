<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Southbay\Product\Api\Data\SouthbayProductChangesHistory as ModelInterface;

class SouthbayProductChangesHistory extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelInterface::TABLE, ModelInterface::ENTITY_ID);
    }
}
