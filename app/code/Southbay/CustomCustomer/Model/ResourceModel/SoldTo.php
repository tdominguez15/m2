<?php
namespace Southbay\CustomCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\CustomCustomer\Api\Data\SoldToInterface;

class SoldTo extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('southbay_sold_to', SoldToInterface::SOUTHBAY_SOLD_TO_ID);
    }
}
