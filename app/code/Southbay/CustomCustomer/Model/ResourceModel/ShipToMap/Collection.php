<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\ShipToMap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\ResourceModel\ShipToMap as ResourceModel;
use Southbay\CustomCustomer\Model\ShipToMap as Model;

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
