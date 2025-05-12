<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\OrderEntryNotification as Model;
use Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotification as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
