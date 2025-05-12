<?php

namespace Southbay\Product\Cron;

use Magento\Framework\App\State;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Api\Data\MapCountryInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\Product\Api\Data\SouthbayProduct;
use Southbay\Product\Model\Import\ProductManager;
use Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig;

class SouthbaySapProductImportCron
{
    private $log;
    private $repository;
    private $collectionFactory;
    private $mapCountryRepository;
    private $sapProductCollectionFactory;
    private $sapProductRepository;
    private $productCollectionFactory;
    private $configStoreRepository;

    private $state;

    private $cache_products = [];
    private $cache_config_store = [];

    private $productManager;
    private $checkCustomIndexerConfig;

    public function __construct(\Psr\Log\LoggerInterface                                                    $log,
                                \Southbay\Product\Model\ResourceModel\ProductSapInterface\CollectionFactory $collectionFactory,
                                \Southbay\Product\Model\ResourceModel\SouthbaySapProduct\CollectionFactory  $sapProductCollectionFactory,
                                \Southbay\Product\Model\ResourceModel\SouthbaySapProduct                    $sapProductRepository,
                                \Southbay\Product\Model\ResourceModel\ProductSapInterface                   $repository,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory              $productCollectionFactory,
                                ConfigStoreRepository                                                       $configStoreRepository,
                                MapCountryRepository                                                        $mapCountryRepository,
                                ProductManager                                                              $productManager,
                                State                                                                       $state,
                                CheckCustomIndexerConfig                                                    $checkCustomIndexerConfig
    )
    {
        $this->log = $log;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->mapCountryRepository = $mapCountryRepository;
        $this->sapProductCollectionFactory = $sapProductCollectionFactory;
        $this->sapProductRepository = $sapProductRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configStoreRepository = $configStoreRepository;
        $this->productManager = $productManager;
        $this->state = $state;
        $this->checkCustomIndexerConfig = $checkCustomIndexerConfig;
    }

    public function run()
    {
        if ($this->checkCustomIndexerConfig->isRunning()) {
            $this->log->warning('Indexer is running');
            return;
        }

        $items = $this->getPending();
        $this->checkCustomIndexerConfig->startImportProducts();

        /**
         * @var \Southbay\Product\Model\ProductSapInterface $item
         */
        foreach ($items as $item) {
            try {
                $now = new \DateTime();
                $item->setStatus('init');
                $item->setStartDate($now->getTimestamp());
                $item->setResultMsg(__('Iniciando importacion'));
                $this->repository->save($item);

                $data = $item->getRawData();
                $data = json_decode($data, true);

                if (!empty($data)) {
                    $this->createOrUpdate($data);
                    $item->setResultMsg(__('Fin proceso importacion'));
                    $item->setStatus('end');
                } else {
                    $item->setResultMsg(__('No fue posible procesar los datos recibidos'));
                    $item->setStatus('error');
                }
            } catch (\Exception $e) {
                $this->log->error('SouthbaySapProductImportCron. Error importing sap products: ', ['item_id' => $item->getId(), 'error' => $e]);
                $item->setStatus('error');
                $item->setResultMsg('Error: ' . $e->getMessage());
            } finally {
                $now = new \DateTime();
                $item->setEndDate($now->getTimestamp());
            }

            $this->repository->save($item);
        }

        $this->checkCustomIndexerConfig->stopImportProducts();
    }

    private function getPending()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('southbay_status', ['eq' => 'ok']);
        $collection->load();

        return $collection->getItems();
    }

    private function createOrUpdate($data)
    {
        $cache_map = [];

        if (isset($data['item']['LOCNR'])) {
            $data['item'] = [$data['item']];
        }

        $data_items = $data['item'];

        foreach ($data_items as $item) {
            if (!isset($cache_map[$item['LOCNR']])) {
                $cache_map[$item['LOCNR']] = $this->mapCountryRepository->findBySapCountryCode($item['LOCNR']);

                if (is_null($cache_map[$item['LOCNR']])) {
                    $cache_map[$item['LOCNR']] = $this->mapCountryRepository->findBySapStockCountryCode($item['LOCNR']);
                }
            }
            /**
             * @var MapCountryInterface $map
             */
            $map = $cache_map[$item['LOCNR']];

            if (is_null($map)) {
                $this->log->error("SouthbaySapProductImportCron. country map not exists: " . $item['LOCNR']);
                break;
            }

            if (!isset($item['IDNLF'])
                || !isset($item['WRF_SIZE1_ATWTB'])
                || !isset($item['MATNR'])
                || !isset($item['MAKTM'])
                || !isset($item['KWERT'])
            ) {
                continue;
            }

            $data = [];
            $data['southbay_catalog_product_country_code'] = $map->getCountryCode();
            $data['southbay_catalog_product_sap_country_code'] = $map->getSapCountryCode();
            $data['southbay_catalog_product_sku'] = trim($item['IDNLF']);
            $data['southbay_catalog_product_size'] = trim($item['WRF_SIZE1_ATWTB']);
            $data['southbay_catalog_product_sku_full'] = $data['southbay_catalog_product_sku'] . '/' . $data['southbay_catalog_product_size'];
            $data['southbay_catalog_product_sku_variant'] = ltrim(trim($item['MATNR']), '0');
            $data['southbay_catalog_product_sku_generic'] = substr($data['southbay_catalog_product_sku_variant'], 0, 8);
            $data['southbay_catalog_product_name'] = trim($item['MAKTM']);
            $data['southbay_catalog_product_color'] = trim($item['WRF_COLOR_ATWTB']);
            $data['southbay_catalog_product_ean'] = trim($item['EAN11'] ?? '');
            $data['southbay_catalog_product_bu'] = trim($item['CATEGORYCODE'] ?? '');
            $data['southbay_catalog_product_gender'] = trim($item['GENDER'] ?? '');
            $data['southbay_catalog_product_age'] = trim($item['MERCH_DEPARTMENT'] ?? '');
            $data['southbay_catalog_product_sport'] = trim($item['WGBEZ'] ?? '');
            $data['southbay_catalog_product_shape_1'] = trim($item['PRODUCT_RANKING'] ?? '');
            $data['southbay_catalog_product_shape_2'] = null;
            $date_from = trim($item['ZZFECHA_INI_TEMP'] ?? '');
            if (!empty($date_from)) {
                $data['southbay_catalog_product_sale_date_from'] = \DateTime::createFromFormat('dmY', $date_from);
            }
            $data['southbay_catalog_product_season_name'] = trim($item['LABOR']) ?? '';
            $data['southbay_catalog_product_season_year'] = trim($item['FORMT']) ?? '';
            $data['southbay_catalog_product_price'] = floatval(trim($item['KWERT']));

            try {
                $this->log->debug('Import product from sap', ['sku' => $data['southbay_catalog_product_sku_full']]);

                /**
                 * @var \Southbay\Product\Model\ResourceModel\SouthbaySapProduct\Collection $collection
                 */
                $collection = $this->sapProductCollectionFactory->create();
                $collection->addFieldToFilter('southbay_catalog_product_sku', ['eq' => $data['southbay_catalog_product_sku']]);
                $collection->addFieldToFilter('southbay_catalog_product_size', ['eq' => $data['southbay_catalog_product_size']]);
                $collection->addFieldToFilter('southbay_catalog_product_sap_country_code', ['eq' => $data['southbay_catalog_product_sap_country_code']]);
                $collection->load();

                /**
                 * @var \Southbay\Product\Model\SouthbaySapProduct $product
                 */
                $product = $collection->getFirstItem();

                /*
                if (is_null($product->getId())) {
                    $price_change = true;
                } else if ($product->getPrice() != $data['southbay_catalog_product_price']) {
                    $price_change = true;
                } else {
                    $price_change = false;
                }
                */

                foreach ($data as $key => $value) {
                    $product->setData($key, $value);
                }

                $this->sapProductRepository->save($product);

                // if ($price_change) {
                $this->updateMagentoProduct($product);
                // }
            } catch (\Exception $e) {
                $this->log->error("SouthbaySapProductImportCron. Error saving product " . $data['southbay_catalog_product_sku'] . ";" . $data['southbay_catalog_product_size'] . ";" . $data['southbay_catalog_product_sap_country_code'] . ": " . $e->getMessage());
                throw $e;
            }
        }
    }

    public function updateMagentoProduct(\Southbay\Product\Model\SouthbaySapProduct $sapProduct)
    {
        if (isset($this->cache_config_store[$sapProduct->getCountryCode()])) {
            $config_store = $this->cache_config_store[$sapProduct->getCountryCode()];
        } else {
            $config_store = $this->configStoreRepository->findStoreByFunctionCodeAndCountry(ConfigStoreInterface::FUNCTION_CODE_AT_ONCE, $sapProduct->getCountryCode());
            $this->cache_config_store[$sapProduct->getCountryCode()] = $config_store;
        }

        if (is_null($config_store)) {
            return;
        }

        if ($sapProduct->getPrice() <= 0) {
            return;
        }

        $this->productManager->updateAttrProduct($sapProduct->getSku(), $sapProduct->getPrice(), SouthbayProduct::ENTITY_PRICE, $config_store->getSouthbayStoreCode());
    }
}
