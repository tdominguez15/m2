<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbaySapDocItemCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbaySapDocItem',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocItem'
        );
    }
}
