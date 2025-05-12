<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\Product\Api\Data\SouthbayProductImportImgHistoryInterface;

class SouthbayProductImportImgHistory extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SouthbayProductImportImgHistoryInterface::TABLE, SouthbayProductImportImgHistoryInterface::ENTITY_ID);
    }
}
