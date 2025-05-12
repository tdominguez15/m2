<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\Product\Api\Data\SeasonInterface;

class Season extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('southbay_season', SeasonInterface::ENTITY_ID);
    }
}
