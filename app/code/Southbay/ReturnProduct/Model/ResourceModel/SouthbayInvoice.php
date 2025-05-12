<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoice as SouthbayIvoiceInterfase;

class SouthbayInvoice extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SouthbayIvoiceInterfase::TABLE, SouthbayIvoiceInterfase::ENTITY_ID);
    }
}
