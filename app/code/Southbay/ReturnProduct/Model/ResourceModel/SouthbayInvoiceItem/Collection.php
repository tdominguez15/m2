<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\SouthbayInvoiceItem', 'Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem');
    }
}
