<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\SoldToMap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\ResourceModel\SoldToMap as ResourceModel;
use Southbay\CustomCustomer\Model\SoldToMap as Model;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
