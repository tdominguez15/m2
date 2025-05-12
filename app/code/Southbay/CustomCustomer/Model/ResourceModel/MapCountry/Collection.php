<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\MapCountry;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Model\MapCountry as MapCountryModel;
use Southbay\CustomCustomer\Model\ResourceModel\MapCountry as MapCountryResourceModel;

/**
 * Class Collection
 * @package Southbay\MapCountry\Model\ResourceModel\MapCountry
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
        $this->_init(MapCountryModel::class, MapCountryResourceModel::class);
    }
}
