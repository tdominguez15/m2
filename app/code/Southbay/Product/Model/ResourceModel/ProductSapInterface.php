<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductSapInterface extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('southbay_product_sap_interface', 'southbay_product_sap_id');
    }
}
