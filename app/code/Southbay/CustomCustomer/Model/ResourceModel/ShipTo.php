<?php
namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ShipTo
 * @package Southbay\ShipTo\Model\ResourceModel
 */
class ShipTo extends AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('southbay_ship_to', 'southbay_ship_to_id');
    }
}
