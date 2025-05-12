<?php

namespace Southbay\Product\Helper;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;

class ProductData extends AbstractHelper
{
    const CACHE_TAG = 'southbay_import_product';

    private $attributeRepository;
    private $productRepository;
    private $storeManager;
    private $configStoreRepository;

    private $cacheManager;

    private $optionFactory;

    private $optionManagement;

    private $categoryRepository;

    private $collectionFactory;

    private $resource;

    public function __construct(Context                                                        $context,
                                \Magento\Framework\App\ResourceConnection                      $resource,
                                \Magento\Framework\App\CacheInterface                          $cacheManager,
                                \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory          $optionFactory,
                                \Magento\Eav\Model\Entity\Attribute\OptionManagement           $optionManagement,
                                AttributeRepositoryInterface                                   $attributeRepository,
                                ProductRepositoryInterface                                     $productRepository,
                                StoreManagerInterface                                          $storeManager,
                                \Magento\Catalog\Model\CategoryRepository                      $categoryRepository,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
                                ConfigStoreRepository                                          $configStoreRepository)
    {
        $this->configStoreRepository = $configStoreRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->cacheManager = $cacheManager;
        $this->optionFactory = $optionFactory;
        $this->optionManagement = $optionManagement;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        parent::__construct($context);
    }


    /**
     * @param $sku
     * @param $use_cache
     * @param $edit_mode
     * @param $store_id
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function findBySku($sku, $use_cache = true, $edit_mode = false, $store_id = null)
    {
        $cache_tag = $sku . ($edit_mode ? '_edit' : '') . ($store_id ? '_' . $store_id : '');
        $cache_tag = $this->generateCacheIdentifier($cache_tag, 'product');

        if ($use_cache) {
            $product = $this->getFromCache($cache_tag);
        } else {
            $product = null;
        }

        if (!$product) {
            $product = $this->productRepository->get($sku, $edit_mode, $store_id);

            if ($use_cache && $product) {
                $this->addToCache($cache_tag, $product);
            }
        }

        return $product;
    }

    public function addValueToAttr($code, $value, $use_cache = true)
    {
        $attr = $this->findAttrByCode($code, $use_cache);
    }

    /**
     * @param $code
     * @param $use_cache
     * @return \Magento\Eav\Api\Data\AttributeInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function findAttrByCode($code, $use_cache = true)
    {
        $cache_tag = $this->generateCacheIdentifier($code, 'attr');

        if ($use_cache) {
            $value = $this->getFromCache($cache_tag);

            if (!is_null($value)) {
                return $value;
            }
        }

        $result = $this->attributeRepository->get(Product::ENTITY, $code);

        if ($use_cache && !is_null($result)) {
            $this->addToCache($cache_tag, $result);
        }

        return $result;
    }

    private function generateCacheIdentifier($name, $from)
    {
        return self::CACHE_TAG . '_' . $from . '_' . strtolower($name);
    }

    private function getFromCache($identifier)
    {
        $value = $this->cacheManager->load($identifier);

        if ($value) {
            return unserialize($value);
        }

        return null;
    }

    private function addToCache($identifier, $value)
    {
        $this->cacheManager->save(serialize($value), $identifier, [self::CACHE_TAG]);
    }

    public function cacheClear()
    {
        $this->cacheManager->clean([self::CACHE_TAG]);
    }

    public function listAllProductsByStoreId($store_id, $store_code, $website_id, $type = ['simple'])
    {
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($store_id);
        $collection->addAttributeToSelect(['sku', 'type_id']);
        $collection->addFieldToFilter('type_id', ['in' => $type]);
        $collection->addWebsiteFilter([$website_id]);
        $collection->load();

        $items = $collection->getItems();
        $result = [];

        foreach ($items as $product) {
            if ($product->getTypeId() == 'simple') {
                $stock = $this->getStockFromStore($product->getSku(), $store_code);
            } else {
                $stock = 0;
            }
            $result[] = [
                'product' => $product,
                'stock' => $stock['qty'] ?? 0
            ];
        }

        return $result;
    }

    public function listProductByStoreId($store_id, $catalog_root_id, $saveAsCSV = false, $csvTarget = '', $sku = '')
    {
        $store = $this->storeManager->getStore($store_id);
        $category = $this->categoryRepository->get($catalog_root_id, $store_id);
        $subCategoryIds = $category->getChildrenCategories()->getAllIds();
        $subCategories = $category->getChildrenCategories()->getItems();
        $map_subCategories = $this->getAllCategoriesFrom($subCategories);

        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($store_id);
        $collection->addAttributeToSelect(['sku', 'type_id', 'name', 'visibility', 'price', 'southbay_price_retail', 'website_ids', 'category_ids']);
        $collection->addFieldToFilter('type_id', ['in' => ['configurable', 'simple']]);

        if (!empty($sku)) {
            $collection->addFieldToFilter('sku', ['like' => $sku . '%']);
        }

        $collection->addCategoriesFilter(['in' => $subCategoryIds]);
        $collection->setOrder('sku', 'ASC');
        $collection->load();

        $products = $collection->getItems();

        $result = [];
        $max_categories = 0;

        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        foreach ($products as $product) {
            $item = [
                'sku' => $product->getSku(),
                'type' => $product->getTypeId(),
                'name' => $product->getName(),
                'price_rt' => $product->getPrice(),
                'price_wh' => $product->getData('southbay_price_retail'),
                'visibility' => $product->getVisibility(),
                'in_website' => in_array($store->getWebsiteId(), $product->getWebsiteIds()) ? 'yes' : 'no'
            ];

            if ($product->getTypeId() == 'simple') {
                $stock = $this->getStockFromStore($product->getSku(), $store->getCode());

                if (!$stock) {
                    $item['stock'] = '';
                    $item['in_stock'] = 'no';
                } else {
                    $item['stock'] = $stock['qty'];
                    $item['in_stock'] = $stock['stock_status'] ? 'yes' : 'no';
                }
            } else {
                $item['stock'] = '';
                $item['in_stock'] = '';
            }

            // $product_category_names = [];
            $product_category_ids = $product->getCategoryIds();
            $total = 0;

            foreach ($product_category_ids as $category_id) {
                if (isset($map_subCategories[$category_id])) {
                    $total++;
                    // $product_category_names[] = $map_subCategories[$category_id];
                    $item['category_' . $total] = $map_subCategories[$category_id];
                }
            }

            $max_categories = max($max_categories, $total);

            $result[] = $item;
        }

        if ($saveAsCSV && !empty($csvTarget)) {
            $first = ['sku', 'type', 'name', 'price_rt', 'price_wh', 'visibility', 'in_website', 'stock', 'in_stock'];

            for ($i = 1; $i <= $max_categories; $i++) {
                $first[] = 'category_' . $i;
            }

            $out = fopen($csvTarget, 'w');

            fputcsv($out, $first, ';');

            foreach ($result as $row) {
                $values = array_values($row);
                fputcsv($out, $values, ';');
            }
            fclose($out);
        }

        return $result;
    }

    public function getStockFromStore($sku, $store_code)
    {
        $connection = $this->resource->getConnection();
        $sql = "SELECT stock_id FROM inventory_stock_sales_channel WHERE code = '$store_code'";
        $stock_id = $connection->fetchOne($sql);

        if (is_null($stock_id)) {
            return null;
        }

        $sql = "SELECT source_code FROM inventory_source_stock_link WHERE stock_id = $stock_id";

        $source_code = $connection->fetchOne($sql);

        if (is_null($source_code)) {
            return null;
        }

        $sql = "SELECT quantity as qty, status as stock_status FROM inventory_source_item WHERE source_code = '$source_code' AND sku = '$sku'";
        $row = $connection->fetchRow($sql);

        if (!$row) {
            return null;
        }


        /*
        $sql = "SELECT qty, stock_status FROM inventory_source_item WHERE product_id = $product_id and stock_id = $stock_id and website_id = $website_id";
        $row = $connection->fetchRow($sql);

        if (!$row) {
            $sql = "SELECT qty, stock_status FROM cataloginventory_stock_status WHERE product_id = $product_id and stock_id = $stock_id and website_id = 0";
            $row = $connection->fetchRow($sql);

            if (!$row) {
                return null;
            }
        }
        */

        return [
            'qty' => floatval($row['qty']),
            'stock_status' => $row['stock_status']
        ];
    }

    public function getAttrValueByStoreIdOrDefaultStore($values, $product_id, $store_id)
    {
        if (isset($values[$product_id][$store_id])) {
            return $values[$product_id][$store_id];
        } else if (isset($values[$product_id][0])) {
            return $values[$product_id][0];
        }

        return null;
    }

    public function getAllCategoriesFrom($subCategories)
    {
        $result = [];
        /**
         * @var CategoryInterface $_category
         */
        foreach ($subCategories as $_category) {
            $result[$_category->getId()] = $_category->getName();
            $_subCategories = $_category->getChildrenCategories()->getItems();

            if (!empty($_subCategories)) {
                $result = $result + $this->getAllCategoriesFrom($_subCategories);
            }
        }

        return $result;
    }
}
