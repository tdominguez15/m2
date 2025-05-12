<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\Product\Api\Data\ProductExclusionInterface;

class ProductExclusion extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProductExclusionInterface::TABLE, ProductExclusionInterface::ENTITY_ID);
    }

}
