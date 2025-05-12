<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem as SouthbayInvoiceItemInterfase;

class SouthbayInvoiceItem extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SouthbayInvoiceItemInterfase::TABLE, SouthbayInvoiceItemInterfase::ENTITY_ID);
    }
}
