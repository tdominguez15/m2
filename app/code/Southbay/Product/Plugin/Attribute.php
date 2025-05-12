<?php

namespace Southbay\Product\Plugin;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\ViewModel\SoldToViewModel;
use Southbay\Product\Model\ProductExclusionRepository;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    private static $init_exec = false;

    private $soldToViewModel;
    private $configStoreRepository;
    private $productExclusionRepository;

    public function __construct(SoldToViewModel                                      $soldToViewModel,
                                ConfigStoreRepository                                $configStoreRepository,
                                ProductExclusionRepository                           $productExclusionRepository,
                                \Magento\Catalog\Model\Layer\Filter\ItemFactory      $filterItemFactory,
                                \Magento\Store\Model\StoreManagerInterface           $storeManager,
                                \Magento\Catalog\Model\Layer                         $layer,
                                \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
                                \Magento\Framework\Filter\StripTags                  $tagFilter,
                                array                                                $data = [])
    {
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $tagFilter, $data);
        $this->soldToViewModel = $soldToViewModel;
        $this->configStoreRepository = $configStoreRepository;
        $this->productExclusionRepository = $productExclusionRepository;
        $this->init();
    }

    public function init()
    {
        if (self::$init_exec) {
            return;
        }

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Psr\Log\LoggerInterface $log
         */
        $log = $objectManager->get('Psr\Log\LoggerInterface');

        $sold_to = $this->soldToViewModel->getSoldToFromSession();

        if (is_null($sold_to)) {
            throw new \Exception('Invalid request. Sold to not found');
        }

        $storeId= $this->_storeManager->getStore()->getId();
        $config = $this->configStoreRepository->findByStoreId($storeId);

        if (is_null($config)) {
            throw new \Exception('Store not configured');
        }

        $country = $config->getSouthbayCountryCode();

        $segmentations = $sold_to->getSegmentation();

        $conditions = [];

        $excludedSkus = $this->productExclusionRepository->getExcludedSkus($storeId);
        if(!empty($excludedSkus)){
            $conditions[] = ['attribute' => 'sku', 'nin' => $excludedSkus];
        }



        if (is_null($segmentations)) {
            $segmentations = ['n/a'];
        } else if ($segmentations == '*all-for-southbay*') {
            $segmentations = [];
        } else {
            $_segmentations = [];
            $parts = explode(',', $segmentations);
            foreach ($parts as $part) {
                $_segmentations[] = $part;
            }
            $segmentations = $_segmentations;
        }
        if(empty($segmentation) && empty($conditions)) {
            self::$init_exec = true;
            return;
        }


        foreach ($segmentations as $segmentation) {
            $conditions[] = [
                'attribute' => 'southbay_channel_level_list',
                'like' => "%;$country:$segmentation;%"
            ];
        }

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if (!empty($conditions)) {
            $productCollection->addAttributeToFilter($conditions);
        }

        self::$init_exec = true;
    }
}
