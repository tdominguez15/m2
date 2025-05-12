<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocInterface;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\ReturnProduct\Model\SouthbaySapDocInterface as ModelInterface;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocInterface as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelInterface::class, ResourceModel::class);
    }
}

