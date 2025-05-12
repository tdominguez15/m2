<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\CustomerConfig;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\CustomerConfig as CustomerConfigModel;
use Southbay\CustomCustomer\Model\ResourceModel\CustomerConfig as CustomerConfigResourceModel;

/**
 * Class Collection
 * @package Southbay\MapCountry\Model\ResourceModel\CustomerConfig
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CustomerConfigModel::class, CustomerConfigResourceModel::class);
    }
}
