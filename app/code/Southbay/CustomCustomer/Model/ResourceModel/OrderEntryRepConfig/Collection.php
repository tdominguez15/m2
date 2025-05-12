<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\OrderEntryRepConfig as Model;
use Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig as ResourceModel;

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
