<?php
namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\MapCountryInterface;

class MapCountry extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('southbay_map_country', MapCountryInterface::SOUTHBAY_MAP_COUNTRY_ID);
    }
}
