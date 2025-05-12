<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbaySapInterfaceConfigCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbaySapInterfaceConfig',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterfaceConfig'
        );
    }
}
