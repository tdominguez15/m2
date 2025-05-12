<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\SoldTo;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\ResourceModel\SoldTo as SoldToResourceModel;
use Southbay\CustomCustomer\Model\SoldTo as SoldToModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'southbay_sold_to_id';

    protected function _construct()
    {
        $this->_init(SoldToModel::class, SoldToResourceModel::class);
    }
}
