<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayExchangeReturnCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayExchangeReturn',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayExchangeReturn'
        );
    }
}
