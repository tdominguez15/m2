<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbaySapDocCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbaySapDoc',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDoc'
        );
    }
}
