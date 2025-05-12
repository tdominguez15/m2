<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotificationConfig;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\OrderEntryNotificationConfig as Model;
use Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotificationConfig as ResourceModel;

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

