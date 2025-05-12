<?php

namespace Southbay\Product\Plugin;

use Southbay\Product\Helper\SegmentFilter;
use Southbay\Product\Model\ProductExclusionRepository;
use Magento\Store\Model\StoreManagerInterface;

class Elasticsearch
{
    /**
     * @var SegmentFilter
     */
    protected $segmentFilter;

    /**
     * @var ProductExclusionRepository
     */
    protected $productExclusionRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Elasticsearch constructor.
     *
     * @param SegmentFilter $segmentFilter
     * @param ProductExclusionRepository $productExclusionRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SegmentFilter $segmentFilter,
        ProductExclusionRepository $productExclusionRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->segmentFilter = $segmentFilter;
        $this->productExclusionRepository = $productExclusionRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Before query plugin.
     *
     * @param object $subject
     * @param array $query
     * @return array
     */
    public function beforeQuery($subject, $query)
    {

        $filteredIds = $this->segmentFilter->filterCollectionIds();


        $storeId = $this->storeManager->getStore()->getId();
        $excludedProductIds = $this->productExclusionRepository->getExcludedProductIds($storeId);



        if ($excludedProductIds && count($excludedProductIds) > 0) {
            $query['body']['query']['bool']['must_not'][] = [
                'ids' => ['values' => $excludedProductIds]
            ];
        }


        if ($filteredIds && count($filteredIds) > 0) {
            $query['body']['query']['bool']['filter'][] = [
                'ids' => ['values' => $filteredIds]
            ];
        }

        return [$query];
    }
}
