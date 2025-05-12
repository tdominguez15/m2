<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;

class SouthbayProductImportHistory extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SouthbayProductImportHistoryInterface::TABLE, SouthbayProductImportHistoryInterface::ENTITY_ID);
    }
}
