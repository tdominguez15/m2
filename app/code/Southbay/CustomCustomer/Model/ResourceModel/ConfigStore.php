<?php
namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;

class ConfigStore extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('southbay_config_store', ConfigStoreInterface::SOUTHBAY_GENERAL_CONFIG_ID);
    }
}

