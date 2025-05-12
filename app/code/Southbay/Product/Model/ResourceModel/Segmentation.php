<?php
namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Segmentation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('southbay_product_segmentation', 'entity_id');
    }
}
