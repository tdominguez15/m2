<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\Product\Api\Data\SeasonTypeInterface;

class SeasonType extends AbstractDb
{
    protected $_uniqueFields = [
        ['field' => SeasonTypeInterface::ENTITY_CODE, 'title' => 'Season Type Code']
    ];

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('southbay_season_type', SeasonTypeInterface::ENTITY_ID);
    }
}
