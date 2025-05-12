<?php

namespace Southbay\Product\Model\ResourceModel;

use Southbay\Product\Api\Data\SouthbayProductsUpdate as EntityInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SouthbayProductsUpdate extends AbstractDb
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
