<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\ConfigStore;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\ConfigStore as ConfigStoreModel;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore as ConfigStoreResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'southbay_general_config_id';

    protected function _construct()
    {
        $this->_init(ConfigStoreModel::class, ConfigStoreResourceModel::class);
    }
}
