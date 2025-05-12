<?php

namespace Southbay\Product\Model;

use Southbay\Product\Model\ResourceModel\ProductExclusion\CollectionFactory;


class ProductExclusionRepository
{
    protected $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function getExcludedSkus($store)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('store', $store);
        return $collection->getColumnValues('sku');
    }
    public function getExcludedProductIds($store)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('store', $store);
        return $collection->getColumnValues('product_id');
    }
}
