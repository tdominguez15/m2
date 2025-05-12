<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem as EntityInterface;

class SouthbayReturnProductItem extends AbstractDb
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
