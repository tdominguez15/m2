<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnFinancialApprovalUsersCollection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Southbay\ReturnProduct\Model\SouthbayReturnFinancialApprovalUsers',
            'Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApprovalUsers'
        );
    }
}
