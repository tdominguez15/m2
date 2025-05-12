<?php

namespace Southbay\Product\Model;

use Southbay\Product\Api\Data\SouthbayProduct;

class SouthbayProductChangesHistoryRepository
{
    private $log;
    private $factory;
    private $repository;
    private $collectionFactory;
    private $sourceItemsBySku;
    private $productHelper;
    private $resource;

    private $productCollectionFactory;

    public function __construct(\Psr\Log\LoggerInterface                                                              $log,
                                \Southbay\Product\Model\SouthbayProductChangesHistoryFactory                          $factory,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductChangesHistory                   $repository,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductChangesHistory\CollectionFactory $collectionFactory,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory                        $productCollectionFactory,
                                \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface                                $sourceItemsBySku,
                                \Southbay\Product\Helper\Data                                                         $productHelper,
                                \Magento\Framework\App\ResourceConnection                                             $resource)
    {
        $this->log = $log;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->productHelper = $productHelper;
        $this->resource = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function saveBySku($sku, $store_id)
    {
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($store_id);
        $collection->addAttributeToSelect($this->getKeys());
        $collection->addFieldToFilter('sku', ['eq' => $sku]);
        $collection->load();

        if ($collection->count() == 0) {
            return;
        }

        $product = $collection->getFirstItem();
        $this->save($product);
    }

    public function save(\Magento\Catalog\Model\Product $product)
    {
        if ($product->getStoreId() == 0) {
            return;
        }

        $keys = $this->getKeys();
        $data = $product->getData();

        $raw_data = [];

        foreach ($keys as $key) {
            $raw_data[$key] = $data[$key] ?? null;
        }

        $store_code = $product->getStore()->getCode();

        if ($product->getTypeId() == 'simple') {
            $source_code = $this->productHelper->getSourceCodeByStoreCode($store_code);
            if (!is_null($source_code)) {
                try {
                    $connection = $this->resource->getConnection();
                    $sku = $product->getSku();
                    $sql = "SELECT quantity FROM inventory_source_item WHERE source_code = '$source_code' AND sku = '$sku'";
                    $raw_data['stock'] = intval($connection->fetchOne($sql));
                } catch (\Exception $e) {
                    $this->log->error('Error getting stock', ['product_id' => $product->getId(), 'store_id' => $product->getStoreId(), 'store_code' => $store_code, 'sku' => $product->getSku(), 'e' => $e]);
                    $raw_data['stock'] = null;
                }
            } else {
                $raw_data['stock'] = null;
            }
        } else {
            $raw_data['stock'] = 0;
        }

        $json = json_encode($raw_data);
        $hash = md5($json);

        $last = $this->getLast($product->getId(), $product->getStoreId());

        if (!is_null($last) && $last->getHash() == $hash) {
            return;
        }

        $this->log->debug('Product updated', ['product_id' => $product->getId(), 'store_id' => $product->getStoreId(), 'store_code' => $store_code, 'sku' => $product->getSku()]);

        /**
         * @var \Southbay\Product\Model\SouthbayProductChangesHistory $model
         */
        $model = $this->factory->create();

        $model->setProductId($product->getId());
        $model->setstoreId($product->getStoreId());
        $model->setJsonData($json);
        $model->setHash($hash);

        $this->repository->save($model);
    }

    /**
     * @param $productId
     * @param $storeId
     * @return \Southbay\Product\Model\SouthbayProductChangesHistory|null
     */
    public function getLast($productId, $storeId)
    {
        /**
         * @var \Southbay\Product\Model\ResourceModel\SouthbayProductChangesHistory\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('product_id', $productId);
        $collection->addFieldToFilter('store_id', $storeId);
        $collection
            ->setPageSize(1)
            ->setCurPage(1);
        $collection->setOrder('entity_id', 'DESC');
        $collection->load();

        if ($collection->count() === 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    private function getKeys()
    {
        return [
            SouthbayProduct::ENTITY_SKU,
            SouthbayProduct::ENTITY_NAME,
            SouthbayProduct::ENTITY_SKU_GENERIC,
            SouthbayProduct::ENTITY_SKU_VARIANT,
            SouthbayProduct::ENTITY_PRICE,
            SouthbayProduct::ENTITY_WEIGHT,
            SouthbayProduct::ENTITY_DEPARTMENT,
            SouthbayProduct::ENTITY_GENDER,
            SouthbayProduct::ENTITY_AGE,
            SouthbayProduct::ENTITY_SPORT,
            SouthbayProduct::ENTITY_SEASON_CODE,
            SouthbayProduct::ENTITY_SILUETA_1,
            SouthbayProduct::ENTITY_SILUETA_2,
            SouthbayProduct::ENTITY_PRICE_RETAIL,
            SouthbayProduct::ENTITY_PURCHASE_UNIT,
            SouthbayProduct::ENTITY_SEGMENTATION,
            SouthbayProduct::ENTITY_COLOR,
            SouthbayProduct::ENTITY_SIZE,
            SouthbayProduct::ENTITY_DESCRIPTION,
            SouthbayProduct::ENTITY_RELEASE_DATE
        ];
    }
}
