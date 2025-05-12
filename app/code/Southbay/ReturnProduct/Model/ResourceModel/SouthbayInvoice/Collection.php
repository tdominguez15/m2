<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\SouthbayInvoice', 'Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice');
    }
}
