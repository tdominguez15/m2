<?php

namespace Southbay\Product\Cron;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\Product\Api\Data\SouthbayProductGroupInterface;
use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;
use Southbay\Product\Model\Import\ProductAttrLoader;
use Southbay\Product\Model\Import\ProductLoader;
use Southbay\Product\Model\Import\SubcategoriesLoader;
use Southbay\Product\Model\Season;
use Southbay\Product\Api\Data\SouthbayProduct;
use Southbay\Product\Model\SouthbayProductImportHistoryFactory;
use Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig;

class SouthbayProductImportCron
{
    const UPLOAD_PATH = 'product/import';
    const CACHE_TAG = 'southbay_import_product';
    const ATTR_SET_NAME = 'Southbay attr set';

    private $log;
    private $collectionFactory;

    private $repository;

    private $filesystem;

    private $subcategoriesLoader;

    private $seasonRepository;

    private $attrLoader;

    private $attrSetCollectionFactory;

    private $scopeConfig;

    private $storeManager;

    private $productLoader;

    private $configStoreRepository;
    private $productImportHistoryFactory;

    public $send_as_asyn = true;

    private $request_for_try = [];
    private $max_retry = 20;
    private $total_retry = [];
    private $fails = [];

    private $checkCustomIndexerConfig;

    public function __construct(\Psr\Log\LoggerInterface                                                             $log,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory\CollectionFactory $collectionFactory,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory                   $repository,
                                \Southbay\Product\Model\ResourceModel\SeasonRepository                               $seasonRepository,
                                SubcategoriesLoader                                                                  $subcategoriesLoader,
                                ProductAttrLoader                                                                    $attrLoader,
                                Filesystem                                                                           $filesystem,
                                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory              $attrSetCollectionFactory,
                                \Magento\Framework\App\Config\ScopeConfigInterface                                   $scopeConfig,
                                StoreManagerInterface                                                                $storeManager,
                                ProductLoader                                                                        $productLoader,
                                ConfigStoreRepository                                                                $configStoreRepository,
                                SouthbayProductImportHistoryFactory                                                  $productImportHistoryFactory,
                                CheckCustomIndexerConfig                                                             $checkCustomIndexerConfig
    )
    {
        $this->log = $log;
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->filesystem = $filesystem;
        $this->subcategoriesLoader = $subcategoriesLoader;
        $this->seasonRepository = $seasonRepository;
        $this->attrLoader = $attrLoader;
        $this->attrSetCollectionFactory = $attrSetCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productLoader = $productLoader;
        $this->configStoreRepository = $configStoreRepository;
        $this->productImportHistoryFactory = $productImportHistoryFactory;
        $this->checkCustomIndexerConfig = $checkCustomIndexerConfig;
    }

    public function run()
    {
        if ($this->checkCustomIndexerConfig->isRunning()) {
            $this->log->warning('Indexer is running');
            return;
        }

        $ids = $this->load();
        $ok = false;

        $this->checkCustomIndexerConfig->startImportProducts();

        foreach ($ids as $id) {
            $collection = $this->collectionFactory->create();

            /**
             * @var SouthbayProductImportHistoryInterface $item
             */
            $item = $collection->getItemById($id);

            if ($item->getStatus() == SouthbayProductImportHistoryInterface::STATUS_INIT) {
                if ($item->getType() == SouthbayProductImportHistoryInterface::TYPE_IMPORT) {
                    $this->import($item);
                } else {
                    $this->update($item);
                }

                if ($item->getStatus() == SouthbayProductImportHistoryInterface::STATUS_END) {
                    $ok = true;
                }
            }
        }

        $this->checkCustomIndexerConfig->stopImportProducts();

        if ($ok) {
            self::reindex($this->log);
            self::cacheClean($this->log);
            self::purgeVarnish($this->log, $this->scopeConfig);
        }
    }

    public function loadFromMemory($list, $storeId, $atOnce)
    {
        $this->checkCustomIndexerConfig->startUpdateProducts();

        $item = $this->productImportHistoryFactory->create();
        $item->setStoreId($storeId);
        $item->setIsAtOnce($atOnce);
        $item->setFile('.');
        $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_INIT);
        $item->setResultMsg('');

        $data = $this->loadData($list);

        $this->log->info('loadFromMemory', ['total_items' => $data['total']]);

        $ok = $this->importProducts($item, $data['items'], $data['options']);

        $this->checkCustomIndexerConfig->stopUpdateProducts();

        if ($ok) {
            self::reindex($this->log);
            self::cacheClean($this->log);
            self::purgeVarnish($this->log, $this->scopeConfig);
        }
    }

    private function update(SouthbayProductImportHistoryInterface $item)
    {
        $ok = false;
        $msg = '';

        $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_START);
        $item->setStartImportDate(date('Y-m-d H:i:s'));

        $this->updateProcess($item, __('Iniciando importación'));

        try {
            $data = $this->readFile($item);
            if ($data) {
                $ok = $this->updateProducts($item, $data['items']);
            }
        } catch (\Exception $e) {
            $this->log->error('Error updating products', ['e' => $e, 'trace' => $e->getTraceAsString()]);
            $msg = __('Ocurrio un error inesperado intentando actualizar los productos');
        }

        if ($ok) {
            $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_END);
            $msg = __('Producto actualizados correctamente');
        } else {
            $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_ERROR);
        }

        $item->setEndImportDate(date('Y-m-d H:i:s'));
        $this->updateProcess($item, $msg);

        return $ok;
    }

    private function import(SouthbayProductImportHistoryInterface $item)
    {
        $ok = false;
        $msg = '';

        $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_START);
        $item->setStartImportDate(date('Y-m-d H:i:s'));

        $this->updateProcess($item, __('Iniciando importación'));

        try {
            $data = $this->readFile($item);
            if ($data) {
                $ok = $this->importProducts($item, $data['items'], $data['options']);
            }
        } catch (\Exception $e) {
            $this->log->error('Error importing products', ['e' => $e, 'trace' => $e->getTraceAsString()]);
            $msg = __('Ocurrio un error inesperado intentando importar los productos');
        }

        if ($ok) {
            $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_END);
            $msg = __('Producto importado correctamente');
        } else {
            $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_ERROR);
        }

        $item->setEndImportDate(date('Y-m-d H:i:s'));
        $this->updateProcess($item, $msg);

        return $ok;
    }

    private function updateProcess(SouthbayProductImportHistoryInterface $item, $msg)
    {
        if ($item->getFile() == '.') {
            return $item;
        }

        if (!empty($msg)) {
            $item->setResultMsg($msg);
        }
        $this->repository->save($item);
    }

    private function load()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SouthbayProductImportHistoryInterface::ENTITY_STATUS, SouthbayProductImportHistoryInterface::STATUS_INIT);
        $collection->addOrder(SouthbayProductImportHistoryInterface::ENTITY_CREATED_AT, 'ASC');
        $collection->load();

        return $collection->getAllIds();
    }

    private function readFile(SouthbayProductImportHistoryInterface $item)
    {
        if ($item->getType() == SouthbayProductImportHistoryInterface::TYPE_IMPORT) {
            $result = $this->getData($item, $item->getFile(), $item->getStartOnLineNumber(), $item->getSkus());
        } else {
            $result = $this->getDataForUpdate($item, $item->getFile(), $item->getAttributeCode(), $item->getStoreId(), $item->getStartOnLineNumber(), $item->getSkus());
        }

        if ($result) {
            $item->setLines($result['total']);
            $this->updateProcess($item, __('Se va a intentar importar %1 productos', $result['total']));
        }

        return $result;
    }

    private function loadData($list)
    {
        $total_items = 0;
        $result = [];
        $options = [];

        foreach ($list as $columns) {
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_COLOR, $columns['color']);
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_SIZE, $columns['size']);

            if (!ProductLoader::checkSku($columns['sku'])) {
                throw new \Exception('Sku invalid: ' . $columns['sku']);
            }

            if (!isset($result[$columns['sku']])) {
                $result[$columns['sku']] = $columns;
                $result[$columns['sku']]['options'] = [];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_COLOR] = $columns['color'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_GROUP] = $columns['group'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_SKU_GENERIC] = $columns['generic'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_SKU_VARIANT] = $columns['generic'];
                $result[$columns['sku']]['items'] = [];
            }

            $result[$columns['sku']]['items'][$columns['sku_full']] = $columns;
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'] = [];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_COLOR] = $columns['color'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_SIZE] = $columns['size'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_GROUP] = $columns['group'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_SKU_GENERIC] = $columns['generic'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_SKU_VARIANT] = $columns['variant'];

            $total_items++;
        }

        return ['items' => $result, 'options' => $options, 'total' => $total_items];
    }

    private function getDataForUpdate(SouthbayProductImportHistoryInterface $item, $file, $attr, $store_id, $start_on_line_number, $skus)
    {
        if (empty($skus)) {
            $skus = [];
        } else {
            $skus = explode(',', $skus);
        }

        $result = [];
        $options = [];

        $path = $this->getPath();
        $file = $path . '/' . $file;

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $lines = 0;
        $total_items = 0;

        $config_store = $this->configStoreRepository->findByStoreId($store_id);

        if (is_null($config_store)) {
            return false;
        }

        $country_code = $config_store->getSouthbayCountryCode();

        foreach ($sheet->getRowIterator() as $row) {
            $lines++;
            if ($lines < $start_on_line_number) {
                continue;
            }

            $column_index = -1;
            $stop = false;

            $columns = [];

            foreach ($row->getCellIterator() as $cell) {
                $column_index++;
                $value = $cell->getValue();

                if (empty($value) && $column_index === 0) {
                    $stop = true;
                    break;
                } else if (empty($value)) {
                    continue;
                }

                $value = trim(strval($value));

                switch ($column_index) {
                    case 0:
                    {
                        $columns['sku'] = $value;
                        break;
                    }
                    case 1:
                    {
                        $columns['new_value'] = $value;
                        break;
                    }
                }
            }

            if (!empty($skus) && !in_array($columns['sku'], $skus)) {
                continue;
            }

            if ($stop) {
                break;
            }

            if (!ProductLoader::checkSku($columns['sku'])) {
                $item->setResultMsg(__('Sku invalido: "%1"', $columns['sku']));
                return false;
            }

            if (!empty($columns['new_value'])) {
                if ($attr == SouthbayProduct::ENTITY_SKU && !ProductLoader::checkSku($columns['new_value'])) {
                    $item->setResultMsg(__('El nuevo sku es invalido: "%1"', $columns['new_value']));
                    return false;
                } else if ($attr == SouthbayProduct::ENTITY_SEGMENTATION) {
                    $columns['new_value'] = $this->getSegmentation($columns['new_value'], $country_code);
                }
                $result[$columns['sku']] = $columns['new_value'];
            }

            $total_items++;
        }

        return ['items' => $result, 'options' => $options, 'total' => $total_items];
    }

    private function getData(SouthbayProductImportHistoryInterface $item, $file, $start_on_line_number, $skus)
    {
        if (empty($skus)) {
            $skus = [];
        } else {
            $skus = explode(',', $skus);
        }

        $result = [];
        $options = [];

        $path = $this->getPath();
        $file = $path . '/' . $file;

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $lines = 0;
        $total_items = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $lines++;
            if ($lines < $start_on_line_number) {
                continue;
            }

            $column_index = -1;
            $stop = false;

            $columns = [];

            foreach ($row->getCellIterator() as $cell) {
                $column_index++;
                $value = $cell->getValue();

                if (empty($value) && $column_index === 0) {
                    $stop = true;
                    break;
                }

                $value = trim(strval($value));

                switch ($column_index) {
                    case 0:
                    {
                        $columns['generic'] = $value;
                        break;
                    }
                    case 1:
                    {
                        $columns['variant'] = $value;
                        break;
                    }
                    case 2:
                    {
                        $columns['sku_full'] = $value;
                        break;
                    }
                    case 3:
                    {
                        $columns['sku'] = $value;
                        break;
                    }
                    case 4:
                    {
                        $columns['ean'] = $value;
                        break;
                    }
                    case 5:
                    {
                        $columns['size'] = $value;
                        break;
                    }
                    case 6:
                    {
                        $columns['group'] = $value;
                        break;
                    }
                    case 7:
                    {
                        $columns['season'] = $value;
                        break;
                    }
                    case 8:
                    {
                        $columns['season_year'] = $value;
                        break;
                    }
                    case 9:
                    {
                        $columns['name'] = $value;
                        break;
                    }
                    case 10:
                    {
                        $columns['color'] = $value;
                        break;
                    }
                    case 11:
                    {
                        $columns['initiative'] = $value;
                        break;
                    }
                    case 12:
                    {
                        if (!is_null($value) && !empty(trim($value))) {
                            $value = trim($value);
                            if (is_string($value) && !ctype_digit($value)) {
                                $value = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value);
                            }
                            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                            $value = $date->format('Y-m-d');
                        } else {
                            $value = null;
                        }

                        $columns['starte_date'] = $value;
                        break;
                    }
                    case 19:
                    {
                        $columns['segmentation'] = $value;
                        break;
                    }
                    case 20:
                    {
                        $columns['source'] = $value;
                        break;
                    }
                    case 21:
                    {
                        $columns['purchase_unit'] = $value;
                        break;
                    }
                    case 22:
                    {
                        $columns['price_rt'] = round(floatval($value), 2);
                        break;
                    }
                    case 23:
                    {
                        $columns['price_wh'] = round(floatval($value), 2);
                        break;
                    }
                    case 24:
                    {
                        $columns['description'] = $value;
                        break;
                    }
                }
            }

            if (!empty($skus) && !in_array($columns['sku'], $skus)) {
                continue;
            }

            if ($stop) {
                break;
            }

            if (!ProductLoader::checkSku($columns['sku'])) {
                $item->setResultMsg(__('Sku invalido: "%1"', $columns['sku']));
                return false;
            }

            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_COLOR, $columns['color']);
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_SIZE, $columns['size']);

            if (!isset($result[$columns['sku']])) {
                $result[$columns['sku']] = $columns;
                $result[$columns['sku']]['options'] = [];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_COLOR] = $columns['color'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_RELEASE_DATE] = $columns['starte_date'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_GROUP] = $columns['group'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_SOURCE] = $columns['source'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_SKU_GENERIC] = $columns['generic'];
                $result[$columns['sku']]['options'][SouthbayProduct::ENTITY_SKU_VARIANT] = $columns['generic'];
                $result[$columns['sku']]['items'] = [];
            }

            $result[$columns['sku']]['items'][$columns['sku_full']] = $columns;
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'] = [];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_COLOR] = $columns['color'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_RELEASE_DATE] = $columns['starte_date'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_GROUP] = $columns['group'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_SOURCE] = $columns['source'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_SIZE] = $columns['size'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_SKU_GENERIC] = $columns['generic'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_SKU_VARIANT] = $columns['variant'];
            $result[$columns['sku']]['items'][$columns['sku_full']]['options'][SouthbayProduct::ENTITY_EAN] = $columns['ean'];

            $total_items++;
        }

        return ['items' => $result, 'options' => $options, 'total' => $total_items];
    }

    private function loadOptionsFromData(&$options, $code, $value)
    {
        if (!isset($options[$code])) {
            $options[$code] = [];
        }

        if (!isset($options[$code][$value])) {
            $options[$code][$value] = true;
        }
    }

    public function getPath()
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        return $mediaDirectory->getAbsolutePath(self::UPLOAD_PATH);
    }

    private function updateProducts(SouthbayProductImportHistoryInterface $item, $products)
    {
        $data_to_send = [];
        $map_options = [];
        $map_groups = [];
        $attr_result = [];
        $import_id = $item->getData('season_import_id');

        foreach ($products as $sku => $new_value) {
            if ($item->getAttributeCode() == SouthbayProduct::ENTITY_COLOR) {
                $value = strtoupper(trim(strval($new_value)));
                if (!isset($map_options[$value])) {
                    $attr_result = $this->attrLoader->load([
                        SouthbayProduct::ENTITY_COLOR => [$value => true]
                    ]);

                    if ($attr_result) {
                        $map_options[$value] = $attr_result[SouthbayProduct::ENTITY_COLOR][$value];
                    } else {
                        continue;
                    }
                }
                $new_value = $map_options[$value];

                $data_to_send[] = [
                    'type' => 'update',
                    'store_id' => $item->getStoreId(),
                    'attr' => $item->getAttributeCode(),
                    'value' => $new_value,
                    'sku' => $sku,
                    'import_id' => $import_id
                ];
            } else if ($item->getAttributeCode() == SouthbayProduct::ENTITY_GROUP) {
                if (!isset($map_groups[$new_value])) {
                    $categories = $this->loadSubcategories([['group' => $new_value]], null, $item->getStoreId());

                    if ($categories === false) {
                        $item->setResultMsg($this->subcategoriesLoader->last_error);
                        return false;
                    }

                    $_products = [
                        $sku => [
                            'group' => $new_value,
                            'options' => [],
                            'items' => []
                        ]
                    ];
                    $attr_result = array_merge($attr_result, $this->loadAttributeOptions($_products, $categories, $map_options));
                    $map_groups[$new_value] = [
                        'options' => $_products[$sku]['options'],
                        'category_ids' => $_products[$sku]['category_ids']
                    ];
                }

                $_options = $map_groups[$new_value]['options'];
                $category_ids = $map_groups[$new_value]['category_ids'];

                $attrs = ['category_ids', SouthbayProduct::ENTITY_GROUP, SouthbayProduct::ENTITY_DEPARTMENT, SouthbayProduct::ENTITY_GENDER, SouthbayProduct::ENTITY_AGE, SouthbayProduct::ENTITY_SPORT, SouthbayProduct::ENTITY_SILUETA_1, SouthbayProduct::ENTITY_SILUETA_2];

                foreach ($attrs as $attr) {
                    $data_to_send[] = [
                        'type' => 'update',
                        'store_id' => $item->getStoreId(),
                        'attr' => $attr,
                        'value' => ($attr == 'category_ids' ? $category_ids : ($attr == SouthbayProduct::ENTITY_GROUP ? $new_value : $attr_result[$attr][$_options[$attr]])),
                        'sku' => $sku,
                        'import_id' => $import_id
                    ];
                }
            } else {
                $data_to_send[] = [
                    'type' => 'update',
                    'store_id' => $item->getStoreId(),
                    'attr' => $item->getAttributeCode(),
                    'value' => $new_value,
                    'sku' => $sku,
                    'import_id' => $import_id
                ];
            }
        }

        return $this->sendRequest($item, $data_to_send);
    }

    private function importProducts(SouthbayProductImportHistoryInterface $item, $products, $options)
    {
        if ($item->getIsAtOnce()) {
            $store = $this->storeManager->getStore($item->getStoreId());
            $config_store = $this->configStoreRepository->findByStoreId($store->getId());

            if (is_null($config_store)) {
                return false;
            }

            $country_code = $config_store->getSouthbayCountryCode();
            $categories = $this->loadSubcategories($products, null, $item->getStoreId());

            if ($categories === false) {
                $item->setResultMsg($this->subcategoriesLoader->last_error);
                return false;
            }

            $season = null;
        } else {
            /**
             * @var Season $season
             */
            $season = $this->seasonRepository->findById($item->getSeasonId());

            if ($season == null) {
                return false;
            }

            $country_code = $season->getCountryCode();
            $categories = $this->loadSubcategories($products, $season, null);

            if ($categories === false) {
                $item->setResultMsg($this->subcategoriesLoader->last_error);
                return false;
            }

            $store = $this->storeManager->getStore($season->getStoreId());
        }

        if (!$categories) {
            return false;
        }

        $attr_options = $this->loadAttributeOptions($products, $categories, $options);

        if (!$attr_options) {
            return false;
        }

        $collection = $this->attrSetCollectionFactory->create();
        $collection->addFieldToFilter('entity_type_id', 4);
        $collection->addFieldToFilter('attribute_set_name', self::ATTR_SET_NAME);

        if ($collection->count() == 0) {
            $this->log->error('Attribute set not found: ' . self::ATTR_SET_NAME);
            $this->updateProcess($item, __('No se encontro el set de atributos para southbay'));
            return false;
        }

        $attr_set = $collection->getFirstItem();
        $attr_size = $this->attrLoader->findAttr(SouthbayProduct::ENTITY_SIZE);

        $data_to_send = [];

        foreach ($products as $product) {
            $options = $product['options'];
            $_options = [];

            $_options[SouthbayProduct::ENTITY_PURCHASE_UNIT] = $product['purchase_unit'] ?? 1;
            $_options[SouthbayProduct::ENTITY_PRICE] = $product['price_wh'];
            $_options[SouthbayProduct::ENTITY_PRICE_RETAIL] = $product['price_rt'];

            if (!is_null($season)) {
                $_options[SouthbayProduct::ENTITY_SEASON_CODE] = $season->getSeasonCode();
            }

            // $_options[SouthbayProduct::ENTITY_SEGMENTATION] = $this->getSegmentation($product['segmentation'], $season->getCountryCode());
            if (!empty($product['segmentation'])) {
                $_options[SouthbayProduct::ENTITY_SEGMENTATION] = $this->getSegmentation($product['segmentation'], $country_code);
            }
            $_options[SouthbayProduct::ENTITY_WEIGHT] = 1;

            foreach ($options as $code => $value) {
                if ($code == SouthbayProduct::ENTITY_SKU_VARIANT ||
                    $code == SouthbayProduct::ENTITY_SKU_GENERIC ||
                    $code == SouthbayProduct::ENTITY_GROUP ||
                    $code == SouthbayProduct::ENTITY_RELEASE_DATE ||
                    $code == SouthbayProduct::ENTITY_SOURCE) {
                    $_options[$code] = $value;
                } else if (!isset($attr_options[$code][$value])) {
                    $this->log->error('Attribute option not found: ', ['code' => $code, 'value' => $value]);
                    $this->updateProcess($item, __('El atributo %1 no contiene el valor %2 para southbay', $code, $value));
                    return false;
                } else {
                    $_options[$code] = $attr_options[$code][$value];
                }
            }

            $product['options'] = $_options;
            $items = [];
            $first = true;

            foreach ($product['items'] as $_product) {
                $options = $_product['options'];
                $_options = [];

                foreach ($product['options'] as $code => $value) {
                    $_options[$code] = $value;
                }

                foreach ($options as $code => $value) {
                    if ($code == SouthbayProduct::ENTITY_SKU_VARIANT ||
                        $code == SouthbayProduct::ENTITY_EAN ||
                        $code == SouthbayProduct::ENTITY_SKU_GENERIC ||
                        $code == SouthbayProduct::ENTITY_GROUP ||
                        $code == SouthbayProduct::ENTITY_RELEASE_DATE ||
                        $code == SouthbayProduct::ENTITY_SOURCE) {
                        $_options[$code] = $value;
                    } else if (!isset($attr_options[$code][$value])) {
                        $this->log->error('Attribute option not found: ', ['code' => $code, 'value' => $value]);
                        $this->updateProcess($item, __('El atributo %1 no contiene el valor %2 para southbay', $code, $value));
                        return false;
                    } else {
                        $_options[$code] = $attr_options[$code][$value];
                        if ($first && $code == SouthbayProduct::ENTITY_SIZE) {
                            $first = false;
                            $product['options'][$code] = $_options[$code];
                        }
                    }
                }

                $_product['options'] = $_options;
                $items[] = $_product;
            }

            $product['items'] = $items;

            $data = [
                'type' => 'products',
                'type_operation' => $item->getTypeOperation(),
                'store_id' => $store->getId(),
                'country_code' => $country_code,
                'at_once' => (bool)$item->getIsAtOnce(),
                'category_ids' => $product['category_ids'],
                'attribute_set_id' => $attr_set->getData('attribute_set_id'),
                'attr_size_id' => $attr_size->getAttributeId(),
                'attr_size_label' => $attr_size->getDefaultFrontendLabel(),
                'product' => $product
            ];

            $data_to_send[] = $data;
        }

        return $this->sendRequest($item, $data_to_send);
    }

    private function loadAttributeOptions(&$products, $groups, $options)
    {
        foreach ($products as &$product) {
            $group_code = $product['group'];
            $group = $groups[$group_code];

            $product['category_ids'] = $group['ids'];
            $product['options'][SouthbayProduct::ENTITY_DEPARTMENT] = $group[SouthbayProductGroupInterface::TYPE_DEPARTMENT];
            $product['options'][SouthbayProduct::ENTITY_GENDER] = $group[SouthbayProductGroupInterface::TYPE_GENDER];
            $product['options'][SouthbayProduct::ENTITY_AGE] = $group[SouthbayProductGroupInterface::TYPE_AGE];
            $product['options'][SouthbayProduct::ENTITY_SPORT] = $group[SouthbayProductGroupInterface::TYPE_SPORT];
            $product['options'][SouthbayProduct::ENTITY_SILUETA_1] = $group[SouthbayProductGroupInterface::TYPE_SHAPE_1];
            $product['options'][SouthbayProduct::ENTITY_SILUETA_2] = $group[SouthbayProductGroupInterface::TYPE_SHAPE_2];

            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_DEPARTMENT, $group[SouthbayProductGroupInterface::TYPE_DEPARTMENT]);
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_GENDER, $group[SouthbayProductGroupInterface::TYPE_GENDER]);
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_AGE, $group[SouthbayProductGroupInterface::TYPE_AGE]);
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_SPORT, $group[SouthbayProductGroupInterface::TYPE_SPORT]);
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_SILUETA_1, $group[SouthbayProductGroupInterface::TYPE_SHAPE_1]);
            $this->loadOptionsFromData($options, SouthbayProduct::ENTITY_SILUETA_2, $group[SouthbayProductGroupInterface::TYPE_SHAPE_2]);

            foreach ($product['items'] as &$item) {
                $item['options'][SouthbayProduct::ENTITY_DEPARTMENT] = $group[SouthbayProductGroupInterface::TYPE_DEPARTMENT];
                $item['options'][SouthbayProduct::ENTITY_GENDER] = $group[SouthbayProductGroupInterface::TYPE_GENDER];
                $item['options'][SouthbayProduct::ENTITY_AGE] = $group[SouthbayProductGroupInterface::TYPE_AGE];
                $item['options'][SouthbayProduct::ENTITY_SPORT] = $group[SouthbayProductGroupInterface::TYPE_SPORT];
                $item['options'][SouthbayProduct::ENTITY_SILUETA_1] = $group[SouthbayProductGroupInterface::TYPE_SHAPE_1];
                $item['options'][SouthbayProduct::ENTITY_SILUETA_2] = $group[SouthbayProductGroupInterface::TYPE_SHAPE_2];
            }
        }

        return $this->attrLoader->load($options);
    }

    private function loadSubcategories($products, $season, $store_id)
    {
        $map = [];

        foreach ($products as $product) {
            $group = $product['group'];
            if (!isset($map[$group])) {
                $department = substr($group, 0, 1);
                $gender = substr($group, 1, 2);
                $age = substr($group, 3, 2);
                $firstSilueta = substr($group, 5, 2);
                $sport = substr($group, 7, 2);
                $secondSilueta = substr($group, 10, 2);

                $map[$group] = [
                    'level_1' => ['code' => $department, 'type' => SouthbayProductGroupInterface::TYPE_DEPARTMENT],
                    'level_2' => ['code' => $gender, 'type' => SouthbayProductGroupInterface::TYPE_GENDER],
                    'level_3' => ['code' => $age, 'type' => SouthbayProductGroupInterface::TYPE_AGE],
                    'level_4' => ['code' => $sport, 'type' => SouthbayProductGroupInterface::TYPE_SPORT],
                    'level_5' => ['code' => $firstSilueta, 'type' => SouthbayProductGroupInterface::TYPE_SHAPE_1],
                    'level_6' => ['code' => $secondSilueta, 'type' => SouthbayProductGroupInterface::TYPE_SHAPE_2]
                ];
            }
        }

        // $this->log->debug('loadSubcategories', ['m' => $map]);

        if (!is_null($season)) {
            return $this->subcategoriesLoader->loadFromSeason($map, $season);
        } else {
            return $this->subcategoriesLoader->loadFromStore($map, $store_id);
        }
    }

    public function sendRequestFromMemory($list, $storeId, $atOnce)
    {
        $this->request_for_try = [];
        $this->total_retry = [];
        $this->fails = [];

        $item = $this->productImportHistoryFactory->create();
        $item->setStoreId($storeId);
        $item->setIsAtOnce($atOnce);
        $item->setFile('.');
        $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_INIT);
        $item->setResultMsg('');

        return $this->sendRequest($item, $list);
    }

    public function sendRequest(SouthbayProductImportHistoryInterface $item, $list)
    {
        $max_request = $this->scopeConfig->getValue('southbay_magento/general/max_request');
        $max_request = intval($max_request);

        if ($max_request <= 0) {
            $max_request = 10;
        }

        $ok = true;
        $chunkedArray = array_chunk($list, $max_request);
        $total = count($chunkedArray);
        $total_send = 0;

        // $this->log->debug('Total productos to saved', ['total' => count($list)]);

        for ($i = 0; $i < $total; $i++) {
            $requests = $chunkedArray[$i];
            if ($this->send_as_asyn) {
                $_total_send = $this->_sendRequest($requests);
            } else {
                $_total_send = $this->_save($requests);
            }

            $total_send += $_total_send['total'];
            $this->log->debug(__('Se procesaron %1 de %2', $total_send, count($list)), ['result' => $_total_send]);

            if ($item->getType() == SouthbayProductImportHistoryInterface::TYPE_IMPORT) {
                $this->updateProcess($item, __('Se procesaron %1 de %2', $total_send, $item->getLines()));
            } else {
                $this->updateProcess($item, __('Actualizando productos...', $total_send, $item->getLines()));
                if ($item->getAttributeCode() == SouthbayProduct::ENTITY_SKU) {
                    $this->productLoader->getProductManager()->updateSalesSkuByImportId($item->getData('season_import_id'));
                }
            }
        }

        $this->log->debug('Total product saved', ['saved' => $total_send, 'total' => count($list)]);

        if (!empty($this->request_for_try)) {
            $this->log->debug('RETRY!!!', ['total' => count($this->request_for_try)]);
            $list = $this->request_for_try;
            $this->request_for_try = [];
            return $this->sendRequest($item, $list);
        } else if (!empty($this->fails)) {
            $this->fails = [];
            $ok = false;
            $item->setResultMsg(__('No fue posible importar alguno de los registros'));
        }

        return $ok;
    }

    private function _save($list)
    {
        $total = 0;

        foreach ($list as $data) {
            try {
                $this->productLoader->load($data);
                $total++;
                $this->log->debug('Product saved.', ['sku' => $data['product']['sku']]);
            } catch (\Exception $e) {
                $this->log->error('Error saving data', ['e' => $e]);
                break;
            }
        }

        return ['total' => $total, 'ok' => true];
    }

    private function _sendRequest($list)
    {
        $url_base = $this->scopeConfig->getValue('southbay_magento/general/url_base');
        $url = $url_base . '/rest/V1/southbay/product/load';

        $promises = [];
        $client = new Client([
            'timeout' => 240,
            'verify' => false
        ]);

        $total_products = [];
        $total_ok = [];

        foreach ($list as $body) {
            $promises[] = $client->postAsync($url, ['json' => ['data' => $body]])->then(
                function ($response) use ($body, &$total_products, &$total_ok) {
                    $json = $response->getBody()->getContents();
                    $response = json_decode($json, true);

                    if ($response && isset($response['status']) && $response['status'] == 'success') {
                        $total_products[] = (isset($body['product']['items']) ? count($body['product']['items']) : 1);
                        $total_ok[] = 1;
                    } else {
                        $this->markForRetry($body);
                    }

                    return $response;
                },
                function ($exception) use ($body) {
                    $this->markForRetry($body);
                    return $exception;
                }
            );
        }

        Promise\Utils::settle($promises)->wait();
        $total = count($list);
        $_total_ok = array_sum($total_ok);
        $_total_products = array_sum($total_products);

        $this->log->debug('Total request send:', [
            'total' => $total,
            'total_ok' => $_total_ok,
            'total_products' => $_total_products,
            'retry' => count($this->request_for_try),
            'fails' => count($this->fails)
        ]);

        return ($total == $_total_ok ? ['total' => $_total_products, 'ok' => true] : ['total' => $_total_products, 'ok' => false]);
    }

    private function markForRetry($body)
    {
        $json = json_encode($body);
        $md5 = md5($json);

        if (!isset($this->total_retry[$md5])) {
            $this->log->debug('Request for retry:', ['request' => $body]);
            $this->total_retry[$md5] = [
                'body' => $body,
                'total' => 0
            ];
        }

        $this->total_retry[$md5]['total']++;
        if ($this->total_retry[$md5]['total'] < $this->max_retry) {
            $this->request_for_try[] = $body;
        } else {
            $this->fails[] = $body;
        }
    }

    public function getSegmentation($value, $country_code)
    {
        $values = explode(';', trim($value));
        $_values = [];
        foreach ($values as $value) {
            if (!isset($_values[$value])) {
                $_values[$value] = ";$country_code:$value;";
            }
        }

        return str_replace(';;', ';', implode('', $_values));
    }

    public static function cacheClean($log, $arg = null)
    {
        $log->info('Starting cache clean...');
        try {
            $cmd = 'php ' . BP . '/bin/magento cache:flush';

            if (!empty($arg)) {
                $cmd .= ' ' . $arg;
            }

            $log->info('Cache cmd: ', [$cmd]);
            $output = shell_exec($cmd);
            $log->info('Cache clear output:');
            $log->info($output);
        } catch (\Exception $e) {
            $log->error('Error during cache clean: ', ['e' => $e]);
        }
    }

    public static function reindex($log)
    {
        $log->info('Starting reindex...');
        try {
            // $output = shell_exec('php ' . BP . '/bin/magento index:reset');
            // $log->info('Reset output:');
            // $log->info($output);
            $output = shell_exec('php ' . BP . '/bin/magento index:reindex');
            $log->info('Reindex output:');
            $log->info($output);
        } catch (\Exception $e) {
            $log->error('Error during reindex: ', ['e' => $e]);
        }
    }

    public static function purgeVarnish($log, $scopeConfig)
    {
        $config_value = $scopeConfig->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TYPE);
        if ($config_value == \Magento\PageCache\Model\Config::VARNISH) {
            $log->info('Starting purge varnish...');
            try {
                shell_exec('curl -X PURGE http://localhost/ -H "Purge-Secret: Purge"');
                $log->info('End purge varnish');
            } catch (\Exception $e) {
                $log->error('Error purge varnish: ', ['e' => $e]);
            }
        }
    }
}
