<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\Product\Api\Data\SouthbaySapProductInterface;

class SouthbaySapProduct extends AbstractDb
{
    protected $_uniqueFields = [
        ['field' => [
            SouthbaySapProductInterface::ENTITY_SKU,
            SouthbaySapProductInterface::ENTITY_SIZE,
            SouthbaySapProductInterface::ENTITY_COUNTRY_CODE
        ],
            'title' => 'Product']
    ];

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SouthbaySapProductInterface::TABLE, SouthbaySapProductInterface::ENTITY_ID);
    }
}
