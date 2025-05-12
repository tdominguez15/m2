<?php

namespace Southbay\Product\Model\Import;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Southbay\Product\Api\Data\SouthbayProduct;
use Southbay\Product\Model\ResourceModel\SouthbayProductsUpdateRepository;
use Southbay\Product\Model\SouthbayProductChangesHistoryRepository;

class ProductManager
{
    private $log;
    private $productRepository;
    private $productFactory;
    private $productOptionFactory;
    private $resource;
    private $productAttrLoader;
    private $state;
    private $storeManager;
    private $mapCountryRepository;
    private $getSourceItemsBySku;
    private $sourceItemsSave;
    private $sourceItemFactory;
    private $getStockBySalesChannel;
    private $salesChannelFactory;
    private $productHelper;
    private $configStoreRepository;
    private $configurableProductType;
    private $action;
    private $step = 'none';
    private $function = '';

    private $collectionFactory;

    private $linkManagement;
    private $productChangesHistoryRepository;

    private $productsUpdateRepository;

    public function __construct(LoggerInterface                                                  $log,
                                \Magento\Catalog\Model\ProductRepository                         $productRepository,
                                \Magento\Catalog\Model\ProductFactory                            $productFactory,
                                \Magento\ConfigurableProduct\Helper\Product\Options\Factory      $productOptionFactory,
                                \Magento\Framework\App\ResourceConnection                        $resource,
                                ProductAttrLoader                                                $productAttrLoader,
                                State                                                            $state,
                                StoreManagerInterface                                            $storeManager,
                                MapCountryRepository                                             $mapCountryRepository,
                                GetSourceItemsBySkuInterface                                     $getSourceItemsBySku,
                                SourceItemsSaveInterface                                         $sourceItemsSave,
                                SourceItemInterfaceFactory                                       $sourceItemFactory,
                                \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface   $getStockBySalesChannel,
                                \Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory $salesChannelFactory,
                                \Southbay\Product\Helper\Data                                    $productHelper,
                                \Southbay\CustomCustomer\Model\ConfigStoreRepository             $configStoreRepository,
                                ConfigurableProductType                                          $configurableProductType,
                                \Magento\Catalog\Model\ResourceModel\Product\Action              $action,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory   $collectionFactory,
                                SouthbayProductChangesHistoryRepository                          $productChangesHistoryRepository,
                                CategoryLinkManagementInterface                                  $linkManagement,
                                SouthbayProductsUpdateRepository                                 $productsUpdateRepository)
    {
        $this->log = $log;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->resource = $resource;
        $this->productAttrLoader = $productAttrLoader;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->mapCountryRepository = $mapCountryRepository;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->productHelper = $productHelper;
        $this->configStoreRepository = $configStoreRepository;
        $this->configurableProductType = $configurableProductType;
        $this->action = $action;
        $this->collectionFactory = $collectionFactory;
        $this->linkManagement = $linkManagement;
        $this->productChangesHistoryRepository = $productChangesHistoryRepository;
        $this->productsUpdateRepository = $productsUpdateRepository;
    }

    /*
    public function createProductOrUpdate($data, $store_id, $exclude = [])
    {
        $this->setFunction(__FUNCTION__);
        $this->setStep('init');

        $this->setStep('find_product');
        $product = $this->findProduct($data['sku'], $store_id);

        if (is_null($product)) {
            $this->setStep('create_product');
            $product = $this->productFactory->create();
            $product->setSku($data['sku']);
            $product->setUrlKey(urlencode($data['sku']));
            $product->setStoreId($store_id);
            $product->setData('tax_class_id', 0);

            $product->setData('quantity_and_stock_status',
                [
                    'use_config_manage_stock' => true,
                    'manage_stock' => 0,
                    "is_in_stock" => true,
                    "qty" => 0
                ]
            );

            $data['status'] = 1;
        }

        return $this->updateProduct($data['sku'], $data, $store_id, $exclude, $product);
    }
    */

    public function updateAttrProduct($sku, $new_value, $attribute_code, $store_id, $import_id = false)
    {
        $this->setFunction(__FUNCTION__);
        $this->setStep('init');

        $this->setStep('findProducts');
        $data = $this->findProducts($sku, $store_id, $attribute_code);

        if (empty($data['skus'])) {
            $this->setStep('stop');
            return;
        }

        $config_store = $this->configStoreRepository->findByStoreId($store_id);

        if (!$config_store) {
            $this->log('error', 'store config not exists: ' . $store_id);
            return;
        }

        $store = $this->storeManager->getStore($store_id);
        $config_map = $this->mapCountryRepository->findByCountryCode($config_store->getSouthbayCountryCode());
        $stock_source = $this->productHelper->getSourcesByStockId($config_map->getStockId());
        $at_once = $this->configStoreRepository->isAtOnceByConfig($config_store);

        if ($attribute_code == SouthbayProduct::ENTITY_SKU) {
            $sku_cache = [];
            list($new_sku) = explode('/', $new_value);
            list($old_sku) = explode('/', $sku);
            foreach ($data['parents'] as $id => $children) {
                foreach ($children as $child) {
                    if (!isset($sku_cache[$child['id']])) {
                        $sku_cache[$child['id']] = $child;

                        $_new_sku = $new_sku . '/' . $child['size'];
                        $_old_sku = $old_sku . '/' . $child['size'];
                        $this->log('debug', 'update sku', ['sku' => $sku, 'id' => $id, 'current_sku' => $_old_sku, 'size' => $child['size'], 'new_sku' => $_new_sku]);

                        if (!$this->productExists($_new_sku)) {
                            $this->action->updateAttributes([$child['id']], ["$attribute_code" => $_new_sku, 'url_key' => urlencode($_new_sku)], $store_id);
                            if (!$at_once) {
                                $this->setStock($_new_sku, 999999, $stock_source, $store->getWebsiteId(), $store->getId(), $config_map->getStockId());
                            }
                        }

                        // $this->updateSalesSku($_new_sku, $child['id']);
                        if ($import_id) {
                            $this->addSkuForUpdate($_new_sku, $child['id'], $import_id);
                        }
                    }
                }
                $this->log('debug', 'update parent sku', ['sku' => $sku, 'id' => $id, 'current_sku' => $old_sku, 'new_sku' => $new_sku]);

                if (!$this->productExists($new_sku)) {
                    $this->action->updateAttributes([$id], ["$attribute_code" => $new_sku, 'url_key' => urlencode($new_sku)], $store_id);
                }
                break;
            }
        } else if ($attribute_code == 'category_ids') {
            try {
                $this->state->setAreaCode('adminhtml');
            } catch (\Exception $e) {
            }

            $this->storeManager->setCurrentStore($store_id);
            $skus = $data['skus'];

            foreach ($data['parents'] as $id => $children) {
                foreach ($children as $child) {
                    $this->linkManagement->assignProductToCategories(
                        $skus[$child['id']],
                        $new_value
                    );
                }

                $this->linkManagement->assignProductToCategories(
                    $skus[$id],
                    $new_value
                );
            }
        } else {
            $values = ["$attribute_code" => $new_value];
            $ids = $data['ids'];

            $this->log('debug', '1- values to update', ['ids' => $ids, 'values' => $values]);
            $this->action->updateAttributes($ids, $values, $store_id);

            if ($attribute_code != SouthbayProduct::ENTITY_SKU_VARIANT) {
                $ids = array_keys($data['parents']);

                $this->log('debug', '2- values to update', ['ids' => $ids, 'values' => $values]);
                $this->action->updateAttributes($ids, $values, $store_id);
            }
        }

        $this->setStep('end');
    }

    private function productExists($sku)
    {
        $connection = $this->resource->getConnection();
        $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku = '$sku'";

        $id = $connection->fetchOne($sql);

        return (bool)$id;
    }

    private function findProducts($sku, $store_id, $attribute_code)
    {
        $skus = [];
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($store_id);
        $collection->addFieldToFilter('sku', ['eq' => "$sku"]);
        if ($attribute_code == SouthbayProduct::ENTITY_SKU_VARIANT || $attribute_code == SouthbayProduct::ENTITY_EAN) {
            $collection->addFieldToFilter('type_id', ['eq' => 'simple']);
        } else if ($attribute_code == SouthbayProduct::ENTITY_SKU) {
            $collection->addFieldToFilter('type_id', ['eq' => 'configurable']);
        } else {
            $collection->addFieldToFilter('type_id', ['in' => ['configurable', 'simple']]);
        }
        $collection->load();

        $products = $collection->getItems();
        $ids = [];
        $parents_to_load = [];
        $parent_map = [];

        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        foreach ($products as $product) {
            if ($product->getTypeId() == 'configurable') {
                $children = $this->getChildren($product);
                $parent_map[$product->getId()] = $children;

                $skus[$product->getId()] = $product->getSku();

                foreach ($children as $child) {
                    $ids[] = $child['id'];
                    $skus[$child['id']] = $child['sku'];
                }
            } else {
                $ids[] = $product->getId();
                $skus[$product->getId()] = $product->getSku();

                if ($attribute_code != SouthbayProduct::ENTITY_SKU_VARIANT) {
                    $parent_id = array_first($this->configurableProductType->getParentIdsByChild($product->getId()));
                    if (!isset($parent_map[$parent_id]) && !in_array($parent_id, $parents_to_load)) {
                        $parents_to_load[] = $parent_id;
                    }
                }
            }
        }

        $parents_to_load = array_filter($parents_to_load, function ($id) use ($parent_map) {
            return !isset($parent_map[$id]);
        });

        if (!empty($parents_to_load)) {
            /**
             * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
             */
            $collection = $this->collectionFactory->create();
            $collection->setStoreId($store_id);
            $collection->addFieldToFilter('entity_id', ['in' => $parents_to_load]);
            $collection->addFieldToFilter('type_id', ['in' => ['configurable']]);
            $collection->load();

            $products = $collection->getItems();

            /**
             * @var \Magento\Catalog\Model\Product $product
             */
            foreach ($products as $product) {
                $children = $this->getChildren($product);
                $parent_map[$product->getId()] = $children;
                foreach ($children as $child) {
                    $ids[] = $child['id'];
                    $skus[$child['id']] = $child['sku'];
                }
            }
        }

        return ['ids' => $ids, 'parents' => $parent_map, 'skus' => $skus];
    }

    private function getChildren($product)
    {
        return array_map(
            function ($child) {
                return [
                    'id' => $child->getId(),
                    'sku' => $child->getSku(),
                    'size' => explode('/', $child->getSku())[1]
                ];
            },
            $this->configurableProductType->getUsedProducts($product)
        );
    }

    /*
    public function updateProduct($sku, $data, $store_id, $exclude = [], $product = null)
    {
        $this->setFunction(__FUNCTION__);
        $this->setStep('init');

        $store = $this->setCurrentStore($store_id);

        if (is_null($product)) {
            $product = $this->findProduct($sku, $store_id);
            if (is_null($product)) {
                $this->setStep('update_product_not_found');
                return false;
            }
            $this->setStep('update_product');
        }

        if (isset($data['attribute_set_id'])) {
            $product->setAttributeSetId($data['attribute_set_id']);
        }

        if ($data['is_parent']) {
            $product->setTypeId('configurable'); // type of product (simple/virtual/downloadable/configurable)
            $product->setVisibility(4);  // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
        } else {
            $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
            $product->setVisibility(1);  // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
        }

        $update_sku = false;

        foreach ($data as $key => $value) {
            if ($key == 'sku' && $store_id != 0) {
                continue;
            }

            if (in_array($key, $exclude)) {
                continue;
            }

            // $this->log('debug', 'update product data', ['k' => $key, 'v' => $value]);
            $product->setData($key, $value);

            if ($key == 'sku') {
                $update_sku = true;
                $product->setUrlKey(urlencode($value));
            }
        }

        if ($store_id != 0) {
            $ids = $product->getWebsiteIds();

            if (!$ids) {
                $product->setWebsiteIds([$store->getWebsiteId()]);
            } else if (!in_array($store->getWebsiteId(), $ids)) {
                $ids[] = $store->getWebsiteId();
                $product->setWebsiteIds($ids);
            }
        }

        if ($update_sku) {
            $this->updateSalesSku($product->getData('sku'), $product->getId());
        }

        return $this->saveProduct($product);
    }
    */

    public function addChild($sku, $parent_sku, $store_id, $attr_size_id)
    {
        $this->setFunction(__FUNCTION__);
        $this->setStep('init');

        $product = $this->findProduct($sku, $store_id);

        if (is_null($product)) {
            $this->log('error', 'Product not found', ['sku' => $sku]);
        }

        $parent = $this->findProduct($parent_sku, $store_id);

        if (is_null($parent)) {
            $this->log('error', 'Product not found', ['parent_sku' => $parent]);
        }

        $extensionAttrs = $parent->getExtensionAttributes();
        $optionsFact = $extensionAttrs->getConfigurableProductOptions();
    }

    public function updateSalesSkuByImportId($import_id)
    {
        $items = $this->productsUpdateRepository->getAllByImportId($import_id);

        foreach ($items as $item) {
            $this->updateSalesSku($item->getSku(), $item->getProductId());
        }

        $this->productsUpdateRepository->deleteByImportId($import_id);
    }

    /**
     * @param $store_id
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setCurrentStore($store_id)
    {
        $this->setStep(__FUNCTION__);
        $this->storeManager->setCurrentStore($store_id);
        return $this->storeManager->getStore();
    }

    private function saveProduct($product)
    {
        $this->setStep('saving product', ['sku' => $product->getSku()]);

        try {
            try {
                $this->state->setAreaCode('adminhtml');
            } catch (\Exception $e) {
            }

            return $this->productRepository->save($product);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function setStep($value)
    {
        $this->step = $value;
    }

    public function findProduct($sku, $store_id)
    {
        $this->setStep('find product');
        $product = null;

        try {
            $product = $this->productRepository->get($sku, true, $store_id);
        } catch (NoSuchEntityException $e) {
        }

        return $product;
    }

    private function setFunction($value)
    {
        if (empty($this->function)) {
            $this->function = $value;
        }
    }

    private function log($level, $message, $context = [])
    {
        $_context = ['function' => $this->function, 'step' => $this->step];

        if (empty($context)) {
            $context = $_context;
        } else {
            $context = array_merge($_context, $context);
        }

        $this->log->log($level, $message, $context);
    }

    private function addSkuForUpdate($new_sku, $product_id, $import_id)
    {
        $model = $this->productsUpdateRepository->getNewModel();
        $model->setSeasonImportId($import_id);
        $model->setSku($new_sku);
        $model->setProductId($product_id);

        $this->productsUpdateRepository->save($model);
    }

    public function updateSalesSku($new_sku, $product_id)
    {
//        $connection = $this->resource->getConnection();
//
//        $query = "UPDATE quote_item SET sku = '$new_sku' WHERE product_id = $product_id";
//        $connection->query($query);
//
//        $this->log->debug($query);
//
//        $query = "UPDATE sales_order_item SET sku = '$new_sku' WHERE product_id = $product_id";
//        $connection->query($query);
//
//        $this->log->debug($query);
//
//        $query = "UPDATE sales_shipment_item SET sku = '$new_sku' WHERE product_id = $product_id";
//        $connection->query($query);

        /*
        $queries = [
            "UPDATE quote_item SET sku = '$new_sku' WHERE product_id = $product_id",
            "UPDATE sales_order_item SET sku = '$new_sku' WHERE product_id = $product_id",
            "UPDATE sales_shipment_item SET sku = '$new_sku' WHERE product_id = $product_id"
        ];
        */

        $connection = $this->resource->getConnection();

        $query = "UPDATE quote_item SET sku = '$new_sku' WHERE product_id = $product_id";
        $connection->query($query);

        $this->log->debug($query);

        $query = "UPDATE sales_order_item SET sku = '$new_sku' WHERE product_id = $product_id";
        $connection->query($query);

        $this->log->debug($query);

        $query = "UPDATE sales_shipment_item SET sku = '$new_sku' WHERE product_id = $product_id";
        $connection->query($query);

        $this->log->debug($query);
    }

    public function setStock($sku, $new_stock, $source_code, $website_id, $store_id, $stock_id)
    {
        $this->setSourceStockBySourceCode($sku, $source_code, $new_stock);
        $this->setSourceStockByStockIdAndSku($sku, $website_id, $stock_id, $new_stock);

        /*
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode($source_code);
        $sourceItem->setSku($sku);

        if ($new_stock > 0) {
            $sourceItem->setStatus(1);
            $sourceItem->setQuantity($new_stock);
        } else {
            $sourceItem->setStatus(0);
            $sourceItem->setQuantity(0);
        }

        list($parent_sku) = explode('/', $sku);

        if ($parent_sku != $sku) {
            $sourceItem2 = $this->sourceItemFactory->create();
            $sourceItem2->setSourceCode($source_code);
            $sourceItem2->setSku($parent_sku);
            $sourceItem2->setStatus(1);
            $sourceItem2->setQuantity(0);

            $this->sourceItemsSave->execute([$sourceItem, $sourceItem2]);
        } else {
            $this->sourceItemsSave->execute([$sourceItem]);
        }
        */

        $this->productChangesHistoryRepository->saveBySku($sku, $store_id);
    }

    public function setSourceStockBySourceCode($sku, $source_code, $new_stock): void
    {
        $connection = $this->resource->getConnection();
//
//        if ($new_stock > 0) {
//            $sql = "REPLACE into inventory_source_item(source_code,sku,quantity,status) values('$source_code','$sku', $new_stock, 1)";
//        } else {
//            $sql = "REPLACE into inventory_source_item(source_code,sku,quantity,status) values('$source_code','$sku', 0, 0)";
//        }
//
//        $connection->query($sql);
        $sql = "
    INSERT INTO inventory_source_item (source_code, sku, quantity, status)
    VALUES (:source_code, :sku, :quantity, :status)
    ON DUPLICATE KEY UPDATE
        quantity = VALUES(quantity),
        status = VALUES(status);
";
        $connection->query($sql, [
            'source_code' => $source_code,
            'sku' => $sku,
            'quantity' => $new_stock > 0 ? $new_stock : 0,
            'status' => $new_stock > 0 ? 1 : 0
        ]);
    }

    public function setSourceStockByStockIdAndSku($sku, $web_site_id, $stock_id, $new_stock)
    {
        list($parent_sku) = explode('/', $sku);

        $connection = $this->resource->getConnection();
        $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku = '$sku'";
        $product_id = $connection->fetchOne($sql);

        if (!$product_id) {
            $this->log->error('Product not found', ['sku' => $sku, 'web_site_id' => $web_site_id, 'stock_id' => $stock_id, 'new_stock' => $new_stock]);
        } else {
            if ($parent_sku != $sku) {
                $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku = '$parent_sku'";
                $parent_product_id = $connection->fetchOne($sql);
            } else {
                $parent_product_id = $product_id;
            }

            $this->setSourceStockByStockId($product_id, $parent_product_id, $web_site_id, $stock_id, $new_stock);
        }
    }

    public function setSourceStockByStockId($product_id, $parent_product_id, $web_site_id, $stock_id, $new_stock)
    {
        $this->checkAndUpdateStockItem($product_id, 99999);

        if ($parent_product_id != null && $product_id != $parent_product_id) {
            $this->checkAndUpdateStockItem($parent_product_id, 0);
        } else {
            $this->log->error('Parent Id not Found ', ['child_id' => $product_id, 'parent_id' => $parent_product_id]);
        }
    }

    private function findCatalogInventoryStockItem($product_id)
    {
        $connection = $this->resource->getConnection();
        $sql = "SELECT manage_stock, is_in_stock FROM cataloginventory_stock_item WHERE product_id = $product_id AND stock_id = 1";
        return $connection->fetchRow($sql);
    }

//TODO $max_sale_qty se puede harcodear a 0?
    private function checkAndUpdateStockItem($product_id, $max_sale_qty)
    {
        $stock_item = $this->findCatalogInventoryStockItem($product_id);

        if (!$stock_item || !$stock_item['manage_stock'] || !$stock_item['is_in_stock']) {
            $this->saveCatalogInventoryStockItem($product_id, $max_sale_qty);
        }
    }

    private function saveCatalogInventoryStockItem($product_id, $max_sale_qty)
    {
        $connection = $this->resource->getConnection();
        $sql = "
        INSERT INTO cataloginventory_stock_item(
            product_id,
            stock_id,
            qty,
            min_qty,
            use_config_min_qty,
            is_qty_decimal,
            backorders,
            use_config_backorders,
            min_sale_qty,
            use_config_min_sale_qty,
            max_sale_qty,
            use_config_max_sale_qty,
            is_in_stock,
            low_stock_date,
            notify_stock_qty,
            use_config_notify_stock_qty,
            manage_stock,
            use_config_manage_stock,
            stock_status_changed_auto,
            use_config_qty_increments,
            qty_increments,
            use_config_enable_qty_inc,
            enable_qty_increments,
            is_decimal_divided,
            website_id
        )
        VALUES(
            :product_id,
            1,
            :qty,
            0,
            1,
            0,
            0,
            1,
            1,
            1,
            :max_sale_qty,
            0,
            1,
            null,
            0,
            0,
            1,
            1,
            1,
            1,
            1,
            1,
            0,
            0,
            0
        )
        ON DUPLICATE KEY UPDATE
            max_sale_qty = :max_sale_qty,
            qty = :qty,
            is_in_stock = 1,
            manage_stock = 1;
    ";

        $connection->query($sql, [
            'qty' => $max_sale_qty,
            'product_id' => $product_id,
            'max_sale_qty' => $max_sale_qty
        ]);
    }

    public function generateUniqueUrlKey($sku, $store_id, $connection)
    {

        $baseUrlKey = strtolower(trim($sku));
        $urlKey = $baseUrlKey;
        $counter = 1;

        do {
            $query = "SELECT COUNT(*) FROM url_rewrite WHERE request_path = :url_key AND store_id = :store_id AND entity_type = 'product'";
            $result = $connection->fetchOne($query, ['url_key' => $urlKey . '.html', 'store_id' => $store_id]);

            if ($result > 0) {
                $urlKey = $baseUrlKey . '-' . $counter++;
            } else {
                break;
            }
        } while (true);

        return $urlKey;
    }
}
