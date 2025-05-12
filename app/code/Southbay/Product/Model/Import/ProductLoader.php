<?php

namespace Southbay\Product\Model\Import;

use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\Product\Api\Data\SouthbayProduct;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

class ProductLoader
{
    const FINAL_PATH = 'fotos';
    const CATALOG_PRODUCT_PATH = 'catalog/product';

    private $repository;
    private $productFactory;

    private $productOptionFactory;

    private $resource;

    private $log;

    private $filesystem;

    private $productAttrLoader;

    private $state;

    private $storeManager;

    private $mapCountryRepository;

    private $getSourceItemsBySku;

    private $sourceItemsSave;
    private $sourceItemFactory;
    private $productRepository;

    private $productHelper;

    private $getStockBySalesChannel;

    private $salesChannelFactory;

    private $configStoreRepository;

    private $configurableProductType;

    private $productManager;

    private $productLoaderImg;

    public function __construct(\Magento\Catalog\Model\ProductRepository                         $repository,
                                \Magento\Catalog\Model\ProductFactory                            $productFactory,
                                \Magento\ConfigurableProduct\Helper\Product\Options\Factory      $productOptionFactory,
                                \Magento\Framework\App\ResourceConnection                        $resource,
                                Filesystem                                                       $filesystem,
                                ProductAttrLoader                                                $productAttrLoader,
                                State                                                            $state,
                                StoreManagerInterface                                            $storeManager,
                                MapCountryRepository                                             $mapCountryRepository,
                                GetSourceItemsBySkuInterface                                     $getSourceItemsBySku,
                                SourceItemsSaveInterface                                         $sourceItemsSave,
                                SourceItemInterfaceFactory                                       $sourceItemFactory,
                                ProductRepositoryInterface                                       $productRepository,
                                \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface   $getStockBySalesChannel,
                                \Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory $salesChannelFactory,
                                \Southbay\Product\Helper\Data                                    $productHelper,
                                \Southbay\CustomCustomer\Model\ConfigStoreRepository             $configStoreRepository,
                                ConfigurableProductType                                          $configurableProductType,
                                ProductManager                                                   $productManager,
                                \Psr\Log\LoggerInterface                                         $log,
                                ProductLoaderImgOptimized                                        $productLoaderImg
    )
    {
        $this->repository = $repository;
        $this->productFactory = $productFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->resource = $resource;
        $this->filesystem = $filesystem;
        $this->log = $log;
        $this->productAttrLoader = $productAttrLoader;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->mapCountryRepository = $mapCountryRepository;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->configStoreRepository = $configStoreRepository;
        $this->configurableProductType = $configurableProductType;
        $this->productManager = $productManager;
        $this->productLoaderImg = $productLoaderImg;
    }

    public function getProductManager()
    {
        return $this->productManager;
    }

    public function load($data)
    {
        $store_id = $data['store_id'];
        $product = $data['product'];
        $type_operation = $data['type_operation'] ?? null;
        $category_ids = $data['category_ids'];
        $attribute_set_id = $data['attribute_set_id'];
        $attr_size_id = $data['attr_size_id'];
        $attr_size_label = $data['attr_size_label'];
        $at_once = $data['at_once'];
        $country_code = $data['country_code'];

        if (!self::checkSku($product['sku'])) {
            return false;
        }

        $connection = $this->resource->getConnection();

        try {
            $this->storeManager->setCurrentStore($store_id);
            $store = $this->storeManager->getStore();

            $result = $this->save($attribute_set_id, $country_code, $at_once, $store->getId(), $store->getCode(), $store->getWebsiteId(), $product, $category_ids, $attr_size_id, $attr_size_label, true, $connection, $type_operation);

            if (!is_null($result)) {
                $this->productLoaderImg->findImgBySku($product['sku'], $connection);
            } else {
                throw new \Exception('Error saving product: ' . $product['sku']);
            }
        } catch (\Exception $e) {
            // $this->log->error('error saving product: ' . $product['sku'], ['request' => $data, 'e' => $e, 'trace' => $e->getTraceAsString()]);
            $this->log->error('error saving product: ' . $product['sku'], ['e' => $e, 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public static function checkSku($sku)
    {
        if (is_null($sku) || empty(trim($sku))) {
            return false;
        }

        $pattern = '/^([a-z|0-9])+-([a-z|0-9])+(\/([a-z|0-9])+)*$/i';

        if (!preg_match($pattern, $sku)) {
            return false;
        }

        return true;
    }

    private function save($attribute_set_id, $country_code, $at_once, $store_id, $store_code, $website_id, $data, $category_ids, $attr_size_id, $attr_size_label, $is_parent, $connection, $type_operation = null)
    {
        try {
            $is_new = false;
            try {
                $product = $this->repository->get($data['sku'], true, $store_id);
            } catch (NoSuchEntityException $e) {
                $is_new = true;
                $product = $this->productFactory->create();
                $product->setSku($data['sku']);
                $product->setAttributeSetId($attribute_set_id);
                $product->setData('tax_class_id', 0);
                $product->setStoreId($store_id);
            }
            $url = $this->productManager->generateUniqueUrlKey($data['sku'], $store_id, $connection);
            $product->setUrlKey(urlencode($url));
            if ($is_parent) {
                $product->setTypeId('configurable'); // type of product (simple/virtual/downloadable/configurable)
                $product->setVisibility(4);  // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
            } else {
                $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
                $product->setVisibility(1);  // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
            }

            $product->setData('quantity_and_stock_status',
                [
                    'use_config_manage_stock' => true,
                    'manage_stock' => 1,
                    "is_in_stock" => true,
                    "qty" => 0
                ]
            );

            /*
            if ($at_once) {
                $product->setData('quantity_and_stock_status', [
                    'use_config_manage_stock' => true,
                    'manage_stock' => 0,
                    "is_in_stock" => true,
                    "qty" => 0
                ]);
            } else {
                $product->setData('quantity_and_stock_status', [
                    'use_config_manage_stock' => false,
                    'manage_stock' => 0,
                    "is_in_stock" => true,
                    "qty" => 0
                ]);
            }
            */

            $product->setName($data['name']);
            $product->setStatus(1); // status enabled/disabled 1/0
            $product->setData('description', $data['description'] ?? '');

            $ids = $product->getWebsiteIds();

            if (!$ids) {
                $ids = [];
            }

            if (!in_array($website_id, $ids)) {
                // $this->log->info('Update website', ['website_id' => $website_id, 'sku' => $product->getSku()]);
                $ids[] = $website_id;
                $product->setWebsiteIds($ids);
            }

            $product->setCategoryIds($category_ids);

            foreach ($data['options'] as $key => $value) {
                if ($key == SouthbayProduct::ENTITY_RELEASE_DATE && !is_null($value) && empty(trim($value))) {
                    $value = null;
                }
                $product->setData($key, $value);
            }

            $product = $this->saveProduct($product, $connection);

            if (is_null($product)) {
                return null;
            }
        } catch (\Exception $e) {
            $this->log->error('error saving product: ' . $data['sku'], ['e' => $e, 'trace' => $e->getTraceAsString()]);
            return null;
        }

        if ($is_parent) {
            try {
                $ids = [];

                // if (!$is_new && $at_once) {
                if (!$is_new && ($at_once || is_null($type_operation) || $type_operation == 'progressive')) {
                    $_ids = $this->configurableProductType->getChildrenIds($product->getId());

                    if (!empty($_ids)) {
                        $_ids = $_ids[0];
                        $_ids = array_keys($_ids);
                        foreach ($_ids as $_id) {
                            if (!is_null($_id)) {
                                $ids[] = $_id;
                            }
                        }
                    }
                }

                $sizes = [];
                foreach ($data['items'] as $_data) {
                    $_data['sku'] = $_data['sku_full'];
                    $child = $this->save($attribute_set_id, $country_code, $at_once, $store_id, $store_code, $website_id, $_data, $category_ids, $attr_size_id, $attr_size_label, false, $connection);

                    if (is_null($child)) {
                        return null;
                    }

                    if (!in_array($child->getId(), $ids)) {
                        $ids[] = $child->getId();
                    } else {
                        continue;
                    }

                    $sizes[] = [
                        'value_index' => $_data['options'][SouthbayProduct::ENTITY_SIZE]
                    ];
                }

                $extensionAttrs = $product->getExtensionAttributes();
                $optionsFact = $extensionAttrs->getConfigurableProductOptions();

                //if (!$is_new && $at_once) {
                if (!$is_new && ($at_once || is_null($type_operation) || $type_operation == 'progressive')) {
                    $found = false;
                    /**
                     * @var \Magento\ConfigurableProduct\Api\Data\OptionInterface $optionFact
                     */
                    $optionFact = null;
                    foreach ($optionsFact as $_option) {
                        if ($_option->getAttributeId() == $attr_size_id) {
                            $found = true;
                            $optionFact = &$_option;
                            break;
                        }
                    }

                    if ($found) {
                        $_sizes = $optionFact->getValues();
                        $_sizes_options = [];
                        foreach ($_sizes as $size) {
                            if (isset($size['value_index'])) {
                                $_sizes_options[$size['value_index']] = $size['value_index'];
                            }
                        }

                        foreach ($sizes as $size) {
                            if (!in_array($size['value_index'], $_sizes_options)) {
                                $_sizes[] = $size;
                            }
                        }

                        $sizes = $_sizes;

                        $optionFact->setvalues($sizes);
                        $extensionAttrs->setConfigurableProductLinks($ids);
                    } else {
                        $optionsFact = null;
                    }
                } else if (!is_null($optionsFact)) {
                    $optionsFact = null;
                }

                if (is_null($optionsFact)) {
                    $optionsFact = $this->productOptionFactory->create([
                        [
                            "attribute_id" => $attr_size_id,
                            "label" => $attr_size_label,
                            "position" => 0,
                            "values" => $sizes
                        ]
                    ]);
                    $extensionAttrs->setConfigurableProductLinks($ids);
                }

                $extensionAttrs->setConfigurableProductOptions($optionsFact);
                $product->setExtensionAttributes($extensionAttrs);

                $product = $this->saveProduct($product, $connection);

                if (is_null($product)) {
                    return null;
                }
            } catch (\Exception $e) {
                $this->log->error('Error saving parent: ' . $data['sku'], ['e' => $e, 'trace' => $e->getTraceAsString()]);
                return null;
            }
        } else {
            $this->setSourceStock($product->getSku(), $store_id, $store_code, $website_id, $at_once);
        }

        return $product;
    }

    public function updateImgBySku($sku, $images)
    {
        $target = $this->getMediaFolder();
        $connection = $this->resource->getConnection();
        try {
            // $connection->beginTransaction();

            $this->updateImg($sku, $target, $images, $connection);

            // $connection->commit();
        } catch (\Exception $e) {
            // $connection->rollBack();
            $this->log->error('error updating product image: ', ['e' => $e->getMessage()]);
        }
    }

//    public function findImgBySku($sku, $connection)
//    {
//        $target = $this->getMediaFolder();
//        $img_folder = $this->getImgFolder();
//
//        $list = glob($img_folder . "/*$sku*.*", GLOB_NOSORT);
//
//        $this->updateImg($sku, $target, $list, $connection);
//    }
//
//    public function updateImg($sku, $target_path, $images, \Magento\Framework\DB\Adapter\AdapterInterface $connection)
//    {
//        $attr_type_image = $this->productAttrLoader->findAttr('image');
//        $attr_type_small_image = $this->productAttrLoader->findAttr('small_image');
//        $attr_type_thumbnail = $this->productAttrLoader->findAttr('thumbnail');
//        $attr_media_gallery = $this->productAttrLoader->findAttr('media_gallery');
//
//        $attr_type_image_id = $attr_type_image->getAttributeId();
//        $attr_type_small_image_id = $attr_type_small_image->getAttributeId();
//        $attr_type_thumbnail_id = $attr_type_thumbnail->getAttributeId();
//        $attr_media_gallery_id = $attr_media_gallery->getAttributeId();
//
//        $sql = "SELECT entity_id FROM `catalog_product_entity` WHERE sku like '$sku%'";
//        $ids = $connection->fetchAll($sql);
//
//        if (empty($ids)) {
//            return;
//        }
//
//        foreach ($ids as $id) {
//            $entity_id = $id['entity_id'];
//
//            $sql = "DELETE FROM catalog_product_entity_media_gallery_value WHERE entity_id = $entity_id";
//            $connection->query($sql);
//
//            $sql = "DELETE FROM catalog_product_entity_media_gallery WHERE value_id in (SELECT value_id FROM catalog_product_entity_media_gallery_value_to_entity WHERE entity_id = $entity_id) and attribute_id = $attr_media_gallery_id";
//            $connection->query($sql);
//
//            $sql = "DELETE FROM catalog_product_entity_media_gallery_value_to_entity WHERE entity_id = $entity_id";
//            $connection->query($sql);
//
//            $sql = "DELETE FROM catalog_product_entity_varchar WHERE entity_id = $entity_id AND attribute_id IN ($attr_type_image_id,$attr_type_small_image_id,$attr_type_thumbnail_id)";
//            $connection->query($sql);
//        }
//
//        $imgs_data = [];
//        foreach ($images as $img) {
//            $real_target_path = $target_path . '/';
//
//            $filename = basename($img);
//            $first = substr($filename, 0, 1);
//            $second = substr($filename, 1, 1);
//
//            $real_target_path .= $first;
//
//            if (!file_exists($real_target_path)) {
//                mkdir($real_target_path);
//            }
//
//            $real_target_path .= '/' . $second;
//
//            if (!file_exists($real_target_path)) {
//                mkdir($real_target_path);
//            }
//
//            $real_target_path .= '/' . $filename;
//
//            copy($img, $real_target_path);
//
//            $sql = "INSERT INTO catalog_product_entity_media_gallery(attribute_id, value, media_type, disabled) VALUES($attr_media_gallery_id, '/$first/$second/$filename','image',0)";
//            $connection->query($sql);
//
//            $sql = 'SELECT LAST_INSERT_ID() as id';
//            $last = $connection->fetchRow($sql);
//
//            $id = $last['id'];
//            $imgs_data[] = ['id' => $id, 'file' => $filename, 'file2' => "/$first/$second/$filename", 'is_base' => false];
//        }
//
//        foreach ($ids as $id) {
//            $position = 1;
//            $first = true;
//            $first_img = null;
//            foreach ($imgs_data as $img) {
//                $entity_id = $id['entity_id'];
//                $img_id = $img['id'];
//
//                $sql = "INSERT INTO catalog_product_entity_media_gallery_value_to_entity (value_id, entity_id) VALUES($img_id, $entity_id)";
//                $connection->query($sql);
//
//                $filename2 = $img['file2'];
//
//                if ($first) {
//                    if (is_null($first_img)) {
//                        $first_img = $filename2;
//                    }
//                    if (str_contains($filename2, 'PV.')) {
//                        $_position = 1;
//                    } else if (str_contains($filename2, 'PHSLH000')) {
//                        $_position = 1;
//                    } else if (str_contains($filename2, 'PHSYM')) {
//                        $_position = 1;
//                    } else if (str_contains($filename2, 'PHSFM')) {
//                        $_position = 1;
//                    } else {
//                        if ($position == 1) {
//                            $position++;
//                        }
//                        $_position = $position;
//                    }
//                } else {
//                    $_position = $position;
//                }
//
//                if ($_position == 1 && $first) {
//                    $first = false;
//                    $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,entity_id, store_id,`value`) VALUES($attr_type_image_id, $entity_id,0,'$filename2'), ($attr_type_small_image_id, $entity_id,0,'$filename2'), ($attr_type_thumbnail_id, $entity_id,0,'$filename2')";
//                    $connection->query($sql);
//                }
//
//                $sql = "INSERT INTO catalog_product_entity_media_gallery_value(value_id, entity_id, store_id, label, position, disabled) VALUES($img_id,  $entity_id, 0, null, $position, 0)";
//                $connection->query($sql);
//
//                if ($_position > 1) {
//                    $position++;
//                }
//            }
//
//            if ($first && !is_null($first_img)) {
//                $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,entity_id, store_id,`value`) VALUES($attr_type_image_id, $entity_id,0,'$first_img'), ($attr_type_small_image_id, $entity_id,0,'$first_img'), ($attr_type_thumbnail_id, $entity_id,0,'$first_img')";
//                $connection->query($sql);
//            }
//        }
//    }
//
//    public function getImgFolder()
//    {
//        $media_folder = self::FINAL_PATH;
//        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
//
//        if (!$mediaDirectory->isExist($media_folder)) {
//            $mediaDirectory->create($media_folder);
//        }
//
//        return $mediaDirectory->getAbsolutePath($media_folder);
//    }
//
//    public function getMediaFolder()
//    {
//        $media_folder = self::CATALOG_PRODUCT_PATH;
//        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
//
//        return $mediaDirectory->getAbsolutePath($media_folder);
//    }
//
    private function saveProduct($product, $connection, $attempts = 0)
    {
        try {
            try {
                $this->state->setAreaCode('adminhtml');
            } catch (\Exception $e) {
            }

            // $this->logData($product->getSku(), $product->getStoreId(), $product->getData());

            $connection->query('SET innodb_lock_wait_timeout = 50');
            // $connection->beginTransaction();
            $attempts++;
            return $this->repository->save($product);
            // $connection->commit();
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            // $connection->rollBack();
            if ($attempts < 5) {
                //      sleep(10);
                sleep(pow(2, $attempts));
                return $this->saveProduct($product, $connection, $attempts);
            } else {
                throw $e;
            }
        } catch (\Exception $e) {
            $this->log->error('Error on saveProduct', ['e' => $e]);
            throw $e;
        }
    }

    /*
    private function logData($sku, $store_id, $data)
    {
        $sku = str_replace('/', '-', $sku);
        file_put_contents(BP . '/var/tmp/' . $sku . '#' . $store_id . '.json', json_encode($data, JSON_PRETTY_PRINT));
    }
    */

    public function setStock($sku, $new_stock, $source_code, $website_id, $store_id, $stock_id)
    {
        $this->productManager->setStock($sku, $new_stock, $source_code, $website_id, $store_id, $stock_id);
    }

    public function setSourceStock($sku, $store_id, $store_code, $website_id, $at_once, $new_stock = 0): void
    {
        $stock_source = $this->productHelper->getSourceCodeByStoreCode($store_code);
        $stock_id = $this->productHelper->getSourcesByStockId($store_id);

        if ($at_once) {
            $this->productManager->setStock($sku, $new_stock, $stock_source, $website_id, $store_id, $stock_id);
        } else {
            $this->productManager->setStock($sku, 999999, $stock_source, $website_id, $store_id, $stock_id);
        }
    }

    public function updateProduct($data)
    {
        $sku = $data['sku'];
        $attr = $data['attr'];
        $value = $data['value'] ?? null;
        $source_code = $data['source_code'] ?? '';
        $store_id = $data['store_id'] ?? '';
        $website_id = $data['website_id'] ?? '';
        $at_once = $data['at_once'] ?? false;
        $from_atp = $data['from_atp'] ?? false;
        $price = $data['price'] ?? false;
        $import_id = $data['import_id'] ?? false;
        // $country_code = $data['country_code'];

        // $this->storeManager->setCurrentStore($store_id);
        // $store = $this->storeManager->getStore();

        switch ($attr) {
            case 'stock':
            {
                // $this->setSourceStock($sku, $store->getCode(), $at_once, $country_code, $value, true);
                $stock_id = $this->productHelper->getSourceCodeByStoreId($store_id);
                $this->setStock($sku, $value, $source_code, $website_id, $store_id, $stock_id);
                // $this->setSourceStockBySourceCode($sku, $source_code, $value);
                // $this->setSourceStockByStockIdAndSku($sku, $website_id, $store_id, $value);

                if ($from_atp && $at_once && $price) {
                    $this->productManager->updateAttrProduct($sku, $price, SouthbayProduct::ENTITY_PRICE, $store_id);
                }
                break;
            }
            default:
            {
                // $this->productManager->updateProduct($sku, $data['product'], $store_id);
                $this->productManager->updateAttrProduct($sku, $value, $attr, $store_id, $import_id);
            }
        }
    }
}
