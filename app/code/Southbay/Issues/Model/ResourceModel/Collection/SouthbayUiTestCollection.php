<?php

namespace Southbay\Issues\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayUiTestCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\Issues\Model\SouthbayUiTest',
            'Southbay\Issues\Model\ResourceModel\SouthbayUiTest'
        );
    }
}
