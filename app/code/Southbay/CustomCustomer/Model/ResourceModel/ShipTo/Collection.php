<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\ShipTo;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\ResourceModel\ShipTo as ShipToResourceModel;
use Southbay\CustomCustomer\Model\ShipTo as ShipToModel;

/**
 * Class Collection
 * @package Southbay\ShipTo\Model\ResourceModel\ShipTo
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection.
     */
    protected function _construct()
    {
        $this->_init(ShipToModel::class, ShipToResourceModel::class);
    }
}
