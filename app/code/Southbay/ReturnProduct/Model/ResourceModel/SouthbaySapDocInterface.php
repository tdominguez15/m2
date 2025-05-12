<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\ReturnProduct\Api\Data\SouthbaySapDocInterface as ModelInterface;

class SouthbaySapDocInterface extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(ModelInterface::TABLE, ModelInterface::ENTITY_ID);
    }
}
