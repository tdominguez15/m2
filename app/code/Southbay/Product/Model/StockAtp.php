<?php

namespace Southbay\Product\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\Product\Cron\SouthbayProductImportCron;
use Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig;
use Southbay\ReturnProduct\Helper\SendSapRtvRequest;
use Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\ResourceModel\MapCountry\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\App\ResourceConnection;

class StockAtp
{
    protected $sendSapRtvRequest;
    protected $logger;
    protected $productRepository;
    protected $productCollectionFactory;
    protected $countryCollectionFactory;
    protected $resourceConnection;
    private $configStoreRepository;
    private $sapProductCollectionFactory;
    private $productImportCron;
    private $storeManager;

    private $categoryRepository;

    private $productHelper;

    private $mapCountryRepository;

    private $atpHistoryRepository;
    private $atpHistoryFactory;
    private $productDataHelper;

    private $checkCustomIndexerConfig;

    public function __construct(
        SendSapRtvRequest                                                          $sendSapRtvRequest,
        ProductRepositoryInterface                                                 $productRepository,
        ProductCollectionFactory                                                   $productCollectionFactory,
        LoggerInterface                                                            $logger,
        CountryCollectionFactory                                                   $countryCollectionFactory,
        ResourceConnection                                                         $resourceConnection,
        ConfigStoreRepository                                                      $configStoreRepository,
        \Southbay\Product\Model\ResourceModel\SouthbaySapProduct\CollectionFactory $sapProductCollectionFactory,
        SouthbayProductImportCron                                                  $productImportCron,
        \Magento\Store\Model\StoreManagerInterface                                 $storeManager,
        \Magento\Catalog\Model\CategoryRepository                                  $categoryRepository,
        \Southbay\Product\Helper\Data                                              $productHelper,
        \Southbay\Product\Helper\ProductData                                       $productDataHelper,
        MapCountryRepository                                                       $mapCountryRepository,
        \Southbay\Product\Model\ResourceModel\SouthbayAtpHistory                   $atpHistoryRepository,
        \Southbay\Product\Model\SouthbayAtpHistoryFactory                          $atpHistoryFactory,
        CheckCustomIndexerConfig                                                   $checkCustomIndexerConfig
    )
    {
        $this->sendSapRtvRequest = $sendSapRtvRequest;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->configStoreRepository = $configStoreRepository;
        $this->sapProductCollectionFactory = $sapProductCollectionFactory;
        $this->productImportCron = $productImportCron;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->productHelper = $productHelper;
        $this->mapCountryRepository = $mapCountryRepository;
        $this->atpHistoryRepository = $atpHistoryRepository;
        $this->atpHistoryFactory = $atpHistoryFactory;
        $this->productDataHelper = $productDataHelper;
        $this->checkCustomIndexerConfig = $checkCustomIndexerConfig;
    }

    public function updateStock()
    {
        // die()

        if ($this->checkCustomIndexerConfig->isRunning()) {
            $this->logger->warning('Indexer is running');
            return;
        }

        $countryCollection = $this->countryCollectionFactory->Create();
        $countryCollection->addFieldToFilter('southbay_map_stock_id', ['notnull' => true]);
        $countryCollection->addFieldToFilter('southbay_map_sap_source_code', ['notnull' => true]);
        $countryCollection->addFieldToFilter('southbay_map_sap_warehouse_code', ['notnull' => true]);
        if (empty($countryCollection->getItems())) {
            $this->logger->info("Stock Update: No se encontro codigo de tienda asociado al solicitar stock atp");
        }

        /**
         * @var \Southbay\CustomCustomer\Model\MapCountry $country
         */
        foreach ($countryCollection as $country) {
            $this->logger->info('============================================');
            $startTime = (int)microtime(true);
            $this->logger->info('Processing started for country: ' . $country->getSouthbayMapSapSourceCode() . ' at ' . date('Y-m-d H:i:s', $startTime));
            try {
                $this->sendStockAtpRequest($country->getSouthbayMapSapSourceCode(), $country->getCountryCode(), $country->getSouthbayMapStockId(), $country->getSouthbayMapSapWarehouseCode());
            } catch (\Exception $e) {
                $this->logger->error('Error processing country: ' . $country->getSouthbayMapSapSourceCode() . ' ', ['error' => $e]);
            }

            $endTime = (int)microtime(true);
            $this->logger->info('Processing ended for country: ' . $country->getSouthbayMapSapSourceCode() . ' at ' . date('Y-m-d H:i:s', $endTime));
            $this->logger->info('Processing time: ' . ($endTime - $startTime) . ' seconds.');

        }
        $this->logger->info('Stock update process ended.');
    }


    public function buildStockAtpRequest($stockStore)
    {


        return [
            'Request' => [
                'IT_WERKS' => [
                    [
                        "LOW" => $stockStore,
                        "SIGN" => "I",      // Siempre I
                        "OPTION" => "EQ",   // Siempre EQ
                        "HIGH" => ""        // Siempre un string vacío
                    ]
                ]
            ]
        ];
    }

    private function saveAtpResponse($response, $countryCode, $sapWarehouse)
    {
        /**
         * @var \Southbay\Product\Model\SouthbayAtpHistory $model
         */
        $model = $this->atpHistoryFactory->create();
        $model->setCountryCode($countryCode);
        $model->setSapCountryCode($sapWarehouse);
        if (is_null($response)) {
            $model->setJsonData("");
        } else {
            $model->setJsonData(json_encode($response));
        }

        $this->atpHistoryRepository->save($model);
    }

    public function sendStockAtpRequest($stockStore, $countryCode, $stockId, $sapWarehouse)
    {
        $request = $this->buildStockAtpRequest($stockStore);

        $response = $this->sendSapRtvRequest->getNewCurl(
            SouthbaySapInterfaceConfig::TYPE_STOCK_ATP,
            $request
        );

        $this->logger->info('Products in magento: ' . count($response));
        $this->saveAtpResponse($response, $countryCode, $stockStore);

        // $productsFound = [];
        $productsNotFound = [];

        if ($response !== null) {
            $config_store = $this->configStoreRepository->findStoreByFunctionCodeAndCountry(ConfigStoreInterface::FUNCTION_CODE_AT_ONCE, $countryCode);

            if (is_null($config_store)) {
                $this->logger->warning('Store not found', ['country_code' => $countryCode]);
                return;
            }

            $config_map = $this->mapCountryRepository->findByCountryCode($config_store->getSouthbayCountryCode());
            $stock_source = $this->productHelper->getSourcesByStockId($config_map->getStockId());

            $this->checkProducts($response, $config_store, $countryCode);
            $productArray = $this->getMaterialsAndSkus();
            $update_stock_request = [];

            $this->checkCustomIndexerConfig->startUpdateProducts();
            $store = $this->storeManager->getStore($config_store->getSouthbayStoreCode());
            $this->resetStock($store->getId(), $store->getCode(), $store->getWebsiteId(), $stockId, $stock_source, $countryCode);

            foreach ($response as $item) {
                $matnr = ltrim($item['MATNR'], '0');
                $stockAtp = (int)$item['STOCK_ATP'];
                $sku = null;

                if (isset($item['LGORT']) && $item['LGORT'] == $sapWarehouse && array_key_exists($matnr, $productArray)) {
                    $sku = $productArray[$matnr];
                    /*
                    $productsFound[] = [
                        'matnr' => $matnr,
                        'sku' => $sku,
                        'stock_atp' => $stockAtp
                    ];
                    */
                    // $this->southbayHelper->updateProductStock($sku, $stockId, $stockAtp);

                    $collection = $this->sapProductCollectionFactory->create();
                    $collection->addFieldToFilter('southbay_catalog_product_sku_variant', $matnr);
                    $collection->addFieldToFilter('southbay_catalog_product_country_code', $config_store->getSouthbayCountryCode());
                    $collection->load();
                    $_product = $collection->getFirstItem();

                    $update_stock_request[] = [
                        'type' => 'update',
                        'attr' => 'stock',
                        'sku' => $sku,
                        'source_code' => $stock_source,
                        'value' => $stockAtp,
                        'store_id' => $config_store->getSouthbayStoreCode(),
                        'stock_id' => $stockId,
                        'website_id' => $store->getWebsiteId(),
                        'at_once' => true,
                        'from_atp' => true,
                        'price' => ($collection->count() > 0 ? $_product->getData('southbay_catalog_product_price') : null),
                        'country_code' => $countryCode
                    ];
                } else {
                    $productsNotFound[] = [
                        'matnr' => $matnr,
                        'stock_atp' => $stockAtp,
                        'LGORT' => $item['LGORT'],
                        'sapWarehouse' => $sapWarehouse
                    ];;
                }
            }

            $this->productImportCron->sendRequestFromMemory($update_stock_request, $config_store->getSouthbayStoreCode(), true);

            $this->checkCustomIndexerConfig->stopUpdateProducts();

            //Resumen
            $this->logger->info('Atp Summary:');
            // $this->logger->info("Products found = " . count($productsFound));
            $this->logger->info("Atp total products update stock = " . count($update_stock_request));
            $this->logger->info("Atp Products not found = " . count($productsNotFound));

            SouthbayProductImportCron::reindex($this->logger);
            SouthbayProductImportCron::cacheClean($this->logger);
        } else {
            $this->logger->error('No se pudo obtener la información de stock desde SAP. Request: ' . json_encode($request));
        }
    }


    protected function getMaterialsAndSkus()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity_varchar');

        $sql = "SELECT v.value AS material, e.sku
            FROM catalog_product_entity e
            JOIN $tableName v
              ON v.entity_id = e.entity_id
              AND v.attribute_id = 175
            WHERE v.value IS NOT NULL";

        $results = $connection->fetchAll($sql);

        $materialSkuMap = [];
        foreach ($results as $row) {
            $materialSkuMap[$row['material']] = $row['sku'];
        }

        return $materialSkuMap;
    }

    private function checkProducts($items, $config_store, $country_code)
    {
        $list = [];
        $skus = [];

        foreach ($items as $item) {
            if (empty(trim($item['EXTWG']))) {
                continue;
            }

            $variant = ltrim(trim($item['MATNR']), '0');

            $collection = $this->sapProductCollectionFactory->create();
            $collection->addFieldToFilter('southbay_catalog_product_sku_variant', $variant);
            $collection->addFieldToFilter('southbay_catalog_product_country_code', $country_code);
            $collection->load();

            if ($collection->count() === 0) {
                $this->logger->warning('Product not found:', ['variant' => $variant, 'country_code' => $country_code]);
                continue;
            }

            /**
             * @var \Southbay\Product\Model\SouthbaySapProduct $sap_product
             */
            $sap_product = $collection->getFirstItem();
            $columns = [];

            $columns['generic'] = $sap_product->getSkuGeneric();
            $columns['variant'] = $sap_product->getSkuVariant();
            $columns['sku_full'] = !empty($sap_product->getSkuFull()) ? $sap_product->getSkuFull() : $sap_product->getSku() . '/' . $sap_product->getSize();
            $columns['sku'] = $sap_product->getSku();
            $columns['ean'] = $sap_product->getEan();
            $columns['size'] = $sap_product->getSize();
            $columns['group'] = $item['EXTWG'];
            $columns['season'] = $sap_product->getSeasonName();
            $columns['season_year'] = $sap_product->getSeasonYear();
            $columns['name'] = $sap_product->getName();
            $columns['color'] = $sap_product->getColor();
            $columns['segmentation'] = '';
            $columns['initiative'] = '';
            // $columns['starte_date'] = $sap_product->getSaleDateFrom();
            $columns['purchase_unit'] = 1;
            $columns['price_rt'] = 0;
            $columns['price_wh'] = $sap_product->getPrice();
            $columns['description'] = '';

            $skus[] = $columns['sku_full'];

            $list[] = $columns;
        }

        $this->logger->info('Total products to update:', ['total' => count($list), 'store_id' => $config_store->getSouthbayStoreCode()]);

        $store = $this->storeManager->getStore($config_store->getSouthbayStoreCode());
        $group = $store->getGroup();

        $catalog_root_id = $group->getRootCategoryId();
        $category = $this->categoryRepository->get($catalog_root_id);
        $subCategoryIds = $category->getChildrenCategories()->getAllIds();

        $product_collection = $this->productCollectionFactory->create();
        $product_collection->addFieldToFilter('sku', ['in' => $skus]);
        $product_collection->addCategoriesFilter(['in' => $subCategoryIds]);
        $product_collection->addFieldToFilter('type_id', ['in' => ['simple']]);
        $product_collection->setStoreId($store->getId());
        $product_collection->load();

        $products = $product_collection->getItems();
        $_skus = [];

        foreach ($products as $product) {
            $_skus[$product->getData('sku')] = true;
        }

        $this->logger->debug('Total products on atonce:', ['total' => count($_skus), 'store_id' => $config_store->getSouthbayStoreCode()]);

        $_list = [];

        foreach ($list as $columns) {
            if (!isset($_skus[$columns['sku_full']])) {
                $_list[] = $columns;
            }
        }

        $list = $_list;

        if (!empty($list)) {
            $this->logger->info('Total products to update after filters:', ['total' => count($list), 'store_id' => $config_store->getSouthbayStoreCode()]);
            // $this->productImportCron->send_as_asyn = false;
            $this->productImportCron->loadFromMemory($list, $config_store->getSouthbayStoreCode(), true);
        } else {
            $this->logger->info('Not products to update:', ['store_id' => $config_store->getSouthbayStoreCode()]);
        }
    }

    private function resetStock($store_id, $store_code, $website_id, $stockId, $stock_source, $countryCode)
    {
        $products = $this->productDataHelper->listAllProductsByStoreId($store_id, $store_code, $website_id);

        $this->logger->info('1- Init Resetting stock:', ['store_id' => $store_id, 'website_id' => $website_id, 'total_products' => count($products)]);

        $update_stock_request = [];
        foreach ($products as $item) {
            if (!$item['stock']) {
                continue;
            }
            $update_stock_request[] = [
                'type' => 'update',
                'attr' => 'stock',
                'sku' => $item['product']->getData('sku'),
                'source_code' => $stock_source,
                'value' => 0,
                'store_id' => $store_id,
                'stock_id' => $stockId,
                'website_id' => $website_id,
                'at_once' => true,
                'country_code' => $countryCode
            ];
        }

        $this->logger->info('2- Resetting stock:', ['store_id' => $store_id, 'website_id' => $website_id, 'total_products' => count($update_stock_request)]);
        $this->productImportCron->sendRequestFromMemory($update_stock_request, $store_id, true);
    }
}
