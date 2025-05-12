<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbaySapCheckStatusCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbaySapCheckStatus',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapCheckStatus'
        );
    }
}
