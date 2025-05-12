<?php

namespace Southbay\Issues\Model\ResourceModel;

use Southbay\Issues\Api\Data\SouthbayUiTest as EntityInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SouthbayUiTest extends AbstractDb
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
