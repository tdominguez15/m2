<?php
namespace Southbay\Product\Model\ResourceModel\Segmentation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\Product\Model\Segmentation;
use Southbay\Product\Model\ResourceModel\Segmentation as SegmentationResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Segmentation::class, SegmentationResource::class);
    }
}
