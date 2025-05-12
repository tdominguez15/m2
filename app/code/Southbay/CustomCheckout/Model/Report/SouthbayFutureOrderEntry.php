<?php

namespace Southbay\CustomCheckout\Model\Report;

use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Sales\Model\Order;
use Southbay\CustomCheckout\Helper\UploadCardData;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Api\Data\MapCountryInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Model\SoldToRepository;
use Southbay\Product\Api\Data\SouthbayProduct;
use Southbay\Product\Model\Import\ProductAttrLoader;
use Southbay\Product\Model\ProductExclusionRepository;
use Southbay\Product\Model\ResourceModel\SeasonRepository;
use Southbay\ReturnProduct\Helper\SendSapRequest;

class SouthbayFutureOrderEntry
{
    private $collectionFactory;
    private $log;
    private $resource;
    private $productRepository;
    private $_cache = [];
    private $_cache_options = [];
    private $_cache_country = [];
    private $_cache_sold_to = [];
    private $_cache_ship_to = [];
    private $_seasons_cache = [];
    private $_cache_product_excluded = [];

    private $countryFactory;

    private $configStoreRepository;

    private $mapCountry;
    private $_mapCountry;

    private $directoryList;
    private $ioFile;

    private $soldToRepository;

    private $mapCountryRepository;

    private $seasonRepository;

    private $productAttrLoader;

    private $productCollectionFactory;

    private $productExclusionRepository;

    private $uploadCardDataHelper;

    private $colors_cache = [];
    private $departments_cache = [];

    public function __construct(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory     $collectionFactory,
                                \Magento\Framework\App\ResourceConnection                      $resource,
                                \Magento\Catalog\Model\ProductRepository                       $productRepository,
                                \Southbay\CustomCustomer\Model\MapCountryRepository            $mapCountryRepository,
                                ConfigStoreRepository                                          $configStoreRepository,
                                SoldToRepository                                               $soldToRepository,
                                CountryFactory                                                 $countryFactory,
                                DirectoryList                                                  $directoryList,
                                IoFile                                                         $ioFile,
                                SeasonRepository                                               $seasonRepository,
                                ProductAttrLoader                                              $productAttrLoader,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                ProductExclusionRepository                                     $productExclusionRepository,
                                UploadCardData                                                 $uploadCardDataHelper,
                                \Psr\Log\LoggerInterface                                       $log)
    {
        $this->resource = $resource;
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->countryFactory = $countryFactory;
        $this->configStoreRepository = $configStoreRepository;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        $this->log = $log;
        $this->soldToRepository = $soldToRepository;
        $this->mapCountryRepository = $mapCountryRepository;
        $this->seasonRepository = $seasonRepository;
        $this->productAttrLoader = $productAttrLoader;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productExclusionRepository = $productExclusionRepository;
        $this->uploadCardDataHelper = $uploadCardDataHelper;

        $this->loadCountryMap();
    }

    private function loadCountryMap()
    {
        $this->_mapCountry = $this->mapCountryRepository->toMap();
        $this->mapCountry = [];
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->countryFactory->create()->getCollection();
        $collection->load();

        foreach ($collection as $item) {
            $this->mapCountry[$item->getId()] = $item->getName();
        }
    }

    public function loadProductsCacheForOrders($ids, $store_id, $connection = null)
    {
        $ids = implode(',', $ids);

        if (is_null($connection)) {
            $connection = $this->resource->getConnection();
        }

        $sql = "SELECT DISTINCT product_id FROM sales_order_item WHERE order_id in ($ids)";
        $_products_ids = $connection->fetchAll($sql);
        $products_ids = [];

        foreach ($_products_ids as $productId) {
            $products_ids[] = $productId['product_id'];
        }

        $this->_cache_options = $this->productAttrLoader->attrToMap(SouthbayProduct::ENTITY_SIZE);
        $this->colors_cache = $this->productAttrLoader->attrToMap(SouthbayProduct::ENTITY_COLOR);
        $this->departments_cache = $this->productAttrLoader->attrToMap(SouthbayProduct::ENTITY_DEPARTMENT);

        $this->loadProductExcluded($store_id);

        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $product_collection
         */
        $product_collection = $this->productCollectionFactory->create();
        $product_collection->addFieldToFilter('type_id', ['in' => ['simple']]);
        $product_collection->addFieldToFilter('entity_id', ['in' => $products_ids]);
        $product_collection->setStoreId($store_id);
        $product_collection->load();

        $product_colors = $product_collection->getAllAttributeValues(SouthbayProduct::ENTITY_COLOR);
        $product_departments = $product_collection->getAllAttributeValues(SouthbayProduct::ENTITY_DEPARTMENT);
        $product_prices = $product_collection->getAllAttributeValues(SouthbayProduct::ENTITY_PRICE);
        $product_price_retails = $product_collection->getAllAttributeValues(SouthbayProduct::ENTITY_PRICE_RETAIL);
        $product_variants = $product_collection->getAllAttributeValues(SouthbayProduct::ENTITY_SKU_VARIANT);
        $products = $product_collection->getItems();

        foreach ($products as $product) {
            $color_option_id = $product_colors[$product->getId()][$store_id] ?? ($product_colors[$product->getId()][0] ?? '');
            $department_option_id = $product_departments[$product->getId()][$store_id] ?? ($product_departments[$product->getId()][0] ?? '');

            $this->_cache[$product->getSku()] = [
                'variant' => $product_variants[$product->getId()][$store_id] ?? ($product_variants[$product->getId()][0] ?? ''),
                'color' => $this->colors_cache[$color_option_id] ?? '',
                'department' => $this->departments_cache[$department_option_id] ?? '',
                'price' => $product_prices[$product->getId()][$store_id] ?? ($product_prices[$product->getId()][0] ?? ''),
                'price_retail' => $product_price_retails[$product->getId()][$store_id] ?? ($product_price_retails[$product->getId()][0] ?? ''),
            ];
        }
    }

    public function generate($data, $export_to_file = true)
    {
        $from = $data['from'];
        $to = $data['to'];
        $store_id = $data['store_id'];
        $sold_to_list = $data['sold_to_list'];

        $this->log->debug('Init generating report', ['from' => $from, 'to' => $to, 'store_id' => $store_id, 'sold_to_list' => $sold_to_list]);

        $config_store = $this->configStoreRepository->findByStoreId($store_id);

        if (is_null($config_store)) {
            $this->log->error('No config store found', ['store_id' => $store_id]);
            return false;
        }

        $map_country = $this->mapCountryRepository->findByCountryCode($config_store->getSouthbayCountryCode());

        if (is_null($map_country)) {
            $this->log->error('No map country found', ['store_id' => $store_id, 'country_code' => $config_store->getSouthbayCountryCode()]);
            return false;
        }

        $sap_channel = SendSapRequest::DEFAULT_SAP_CHANNEL;
        $sap_doc = SendSapRequest::DEFAULT_SAP_DOC;
        $sap_zone = null;

        if (!empty($map_country->getSapChannel())) {
            $sap_channel = $map_country->getSapChannel();
        }

        if (!empty($map_country->getSapZone())) {
            $sap_zone = $map_country->getSapZone();
        }

        if ($config_store->getSouthbayFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
            if (!empty($map_country->getSapAtOnceDoc())) {
                $sap_doc = $map_country->getSapAtOnceDoc();
            }
        } else {
            if (!empty($map_country->getSapFutureDoc())) {
                $sap_doc = $map_country->getSapFutureDoc();
            }
        }

        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('created_at', ['gteq' => $from]);
        $collection->addFieldToFilter('created_at', ['lteq' => $to]);
        $collection->addFieldToFilter('store_id', ['eq' => $store_id]);
        $collection->addFieldToFilter('status', ['neq' => Order::STATE_CANCELED]);
        $collection->load();

        $orders = $collection->getItems();
        $total = count($orders);
        $count = 0;

        $this->log->debug('Start generating report', ['orders' => $total]);
        $connection = $this->resource->getConnection();

        if ($total > 0) {
            $ids = $collection->getAllIds();

            $this->loadProductsCacheForOrders($ids, $store_id, $connection);
        }

        $result = [];

        $season_at_once = null;

        if ($config_store->getSouthbayFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
            $season_at_once = [
                'atonce' => ""
            ];
        }

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        foreach ($orders as $order) {
            $count++;
            $this->log->debug('Reading order (' . $count . '/' . $total . ')', ['order_id' => $order->getId(), 'increment_id' => $order->getIncrementId()]);

            if (!empty($sold_to_list) && !in_array($order->getExtCustomerId(), $sold_to_list)) {
                continue;
            }

            $data = $this->getSeasonDetail($order, $sap_zone, $sap_channel, $sap_doc, $connection, $season_at_once);

            if (!empty($data)) {
                $result[] = $data;
            }
        }

        if ($export_to_file) {
            $base_path = 'export';
            $directoryPath = $this->directoryList->getPath('var') . '/' . $base_path;

            if (!$this->ioFile->fileExists($directoryPath, false)) {
                $this->ioFile->mkdir($directoryPath, 0775);
            }

            $file = $directoryPath . '/result-' . date('Y-m-d') . '.csv';
            $first = true;

            file_put_contents($file, "");

            foreach ($result as $order_data) {
                $str = "";
                foreach ($order_data as $item) {
                    if ($first) {
                        $first = false;
                        $keys = array_keys($item);

                        foreach ($keys as $key) {
                            if ($config_store->getSouthbayFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE
                                && $key == 'flow') {
                                continue;
                            }

                            if (empty($str)) {
                                $str = $key;
                            } else {
                                $str .= ';' . $key;
                            }
                        }
                        $str .= "\n";
                    }

                    $str_row = '';
                    foreach ($item as $key => $value) {
                        if ($config_store->getSouthbayFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE
                            && $key == 'flow') {
                            continue;
                        }

                        if (!empty($str_row)) {
                            $str_row .= ';';
                        }
                        $str_row .= $value;
                    }

                    $str .= $str_row . "\n";
                }

                file_put_contents($file, $str, FILE_APPEND);
            }

            return $file;
        } else {
            return $result;
        }
    }

    public function getSeasonDetail(\Magento\Sales\Model\Order $order, $sap_zone, $sap_channel, $sap_doc, \Magento\Framework\DB\Adapter\AdapterInterface $connection, $season = null)
    {
        if (is_null($season)) {
            $season_id = $order->getExtOrderId();
            if (!isset($this->_seasons_cache[$season_id])) {
                $this->_seasons_cache[$season_id] = $this->seasonRepository->find($season_id);
            }

            /**
             * @var \Southbay\Product\Model\Season $season
             */
            $season = $this->_seasons_cache[$season_id];
        }

        if (is_null($season)) {
            $this->log->error('Season not found: ' . $season_id, ['order_id' => $order->getId(), 'increment_id' => $order->getIncrementId()]);
            return [];
        }

        $result = [];

        $order_id = $order->getId();
        $sql = "SELECT sku, name, product_options  FROM sales_order_item WHERE order_id = $order_id";
        $items = $connection->fetchAll($sql);

        $this->log->debug('Start reading order ', ['order_id' => $order->getId(), 'increment_id' => $order->getIncrementId(), 'items' => count($items)]);

        $dm_id = $order->getBillingAddress()->getVatId();

        if (!isset($this->_cache_ship_to[$dm_id])) {
            $sql = "SELECT southbay_ship_to_customer_code, southbay_ship_to_code, southbay_ship_to_name FROM southbay_ship_to WHERE southbay_ship_to_id = $dm_id";
            $this->_cache_ship_to[$dm_id] = $connection->fetchRow($sql);
        }

        $dm = $this->_cache_ship_to[$dm_id];

        if (empty($dm)) {
            $this->log->error('DM not found', ['order_id' => $order->getId(), 'increment_id' => $order->getIncrementId(), 'dm_id' => $dm_id]);
            return [];
        }

        $sold_to_code = $dm['southbay_ship_to_customer_code'] ?? '';

        if (empty($sold_to_code)) {
            $this->log->error('Sold to code invalid', ['order_id' => $order->getId(), 'increment_id' => $order->getIncrementId(),
                'dm' => $dm]);
            return [];
        }

        if (!isset($this->_cache_sold_to[$sold_to_code])) {
            $sold_to = $this->soldToRepository->getByCustomerCode($sold_to_code);
            $segmentations = $sold_to->getSegmentation();
            if (!empty($segmentations)) {
                $segmentations = strtoupper($segmentations);
                $segmentations = explode(',', $segmentations);
            } else {
                $segmentations = [];
            }
            $this->_cache_sold_to[$sold_to_code] = $segmentations;
        }

        if (isset($this->_cache_product_excluded[$order->getStoreId()])) {
            $exclude_sku = $this->_cache_product_excluded[$order->getStoreId()];
        } else {
            $exclude_sku = [];
        }

        foreach ($items as $item) {
            $product_options = json_decode($item['product_options'], true);

            if (!empty($product_options)) {
                if (isset($product_options['ignore']) || isset($product_options['info_buyRequest']['ignore'])) {
                    continue;
                }

                $info = $product_options['info_buyRequest'];
                $sku_parts = explode('/', $item['sku']);
                $sku = $sku_parts[0];
                $months = [];

                if (in_array($sku, $exclude_sku)) {
                    continue;
                }

                foreach ($info as $key => $values) {
                    if (!in_array($key, ['month_delivery_date_1', 'month_delivery_date_2', 'month_delivery_date_3', 'atonce'])) {
                        continue;
                    }

                    foreach ($values as $size_option_id => $qty) {
                        if ($qty > 0) {
                            if (!isset($months[$key])) {
                                $months[$key] = [];
                            }

                            $size_label = $this->_cache_options[$size_option_id];
                            $sku_full = $sku . '/' . $size_label;

                            if (in_array($sku_full, $exclude_sku)) {
                                continue;
                            }

                            if (!isset($this->_cache[$sku_full])) {
                                $this->loadSkuToCache($sku_full, $order->getStoreId());
                            }

                            $months[$key][$size_option_id] = [
                                'label' => $this->_cache_options[$size_option_id],
                                'qty' => $qty,
                                'sku' => $sku_full,
                                'name' => $item['name'],
                                'southbay_color' => $this->_cache[$sku_full]['color'],
                                'southbay_department' => $this->_cache[$sku_full]['department'],
                                'price' => $this->_cache[$sku_full]['price'],
                                'southbay_price_retail' => $this->_cache[$sku_full]['price_retail'],
                                'variant' => $this->_cache[$sku_full]['variant']
                            ];
                        }
                    }
                }

                if (empty($months)) {
                    continue;
                }

                if (!isset($this->_cache_country[$order->getStoreId()])) {
                    $config = $this->configStoreRepository->findByStoreId($order->getStoreId());
                    $country_name = $this->mapCountry[$config->getSouthbayCountryCode()];
                    $this->_cache_country[$order->getStoreId()] = [
                        'country' => $config->getSouthbayCountryCode(),
                        'name' => $country_name,
                        'frontera' => null
                    ];
                    $country_code_frontera = $config->getSouthbayCountryCode() . '_FRONTERA';
                    if (isset($this->_mapCountry[$country_code_frontera])) {
                        $this->_cache_country[$order->getStoreId()]['frontera'] = $country_code_frontera;
                    }
                }

                $segmentations = $this->_cache_sold_to[$sold_to_code];
                $country_data = $this->_cache_country[$order->getStoreId()];
                $channel = null;
                $doc = null;
                $country_code = $country_data['country'];
                $country_name = $country_data['name'];

                if (in_array('FRONTERA', $segmentations)) {
                    if (!empty($country_data['frontera'])) {
                        $country_code = $country_data['frontera'];
                        $channel = MapCountryInterface::FRONTERA_CHANNEL;
                        $doc = MapCountryInterface::FRONTERA_ORDER_ENTRY_DOC;
                    }
                }

                if (!empty($sap_zone)) {
                    $country_code = $sap_zone;
                }

                if (!empty($sap_channel)) {
                    $channel = $sap_channel;
                }

                if (!empty($sap_doc)) {
                    $doc = $sap_doc;
                }

                foreach ($months as $key => $month_item) {
                    foreach ($month_item as $_item) {
                        $result[] = [
                            'id' => $order->getId(),
                            'created_at' => $order->getCreatedAt(),
                            'customer_order_id' => $order->getIncrementId(),
                            'email' => $order->getCustomerEmail(),
                            'country' => $country_code,
                            'country_name' => $country_name,
                            'channel' => $channel,
                            'doc' => $doc,
                            'flow' => $season[$key],
                            'so' => $dm['southbay_ship_to_customer_code'],
                            'dm' => $dm['southbay_ship_to_code'],
                            'dm_name' => $dm['southbay_ship_to_name'],
                            'variant' => $_item['variant'],
                            'sku' => $sku_parts[0],
                            'size' => $_item['label'],
                            'name' => $_item['name'],
                            'color' => $_item['southbay_color'],
                            'department' => $_item['southbay_department'],
                            'qty' => $_item['qty'],
                            'price_wh' => $_item['price'],
                            'price_rt' => $_item['southbay_price_retail']
                        ];
                    }
                }
            }
        }

        return $result;
    }

    private function getOptionValue($option_id, $store_id, $connection)
    {
        $key = $option_id . '-' . $store_id;

        if (!isset($this->_cache_options[$key])) {
            $sql = "SELECT store_id, option_id, `value` FROM eav_attribute_option_value WHERE option_id in ($option_id) AND store_id = $store_id";
            $_list = $connection->fetchAll($sql);

            if (count($_list) == 0) {
                $sql = "SELECT store_id, option_id, `value` FROM eav_attribute_option_value WHERE option_id in ($option_id) AND store_id = 0";
                $_list = $connection->fetchAll($sql);
            }

            $this->_cache_options[$key] = null;

            foreach ($_list as $_row) {
                if ($_row['option_id'] == $option_id) {
                    $this->_cache_options[$key] = $_row['value'];
                    break;
                }
            }
        } else {
            // $this->log->debug('##FROM CACHE: ' . $key);
        }

        return $this->_cache_options[$key];
    }

    private function loadProductExcluded($store_id)
    {
        if (!isset($this->_cache_product_excluded[$store_id])) {
            $this->_cache_product_excluded[$store_id] = $this->productExclusionRepository->getExcludedSkus($store_id);
        }
    }

    private function loadSkuToCache($sku, $store_id)
    {
        $product = $this->uploadCardDataHelper->findProductBySku($sku, $store_id);

        $color_option_id = $product->getData(SouthbayProduct::ENTITY_COLOR);
        $department_option_id = $product->getData(SouthbayProduct::ENTITY_DEPARTMENT);

        $this->_cache[$product->getSku()] = [
            'variant' => $product->getData(SouthbayProduct::ENTITY_SKU_VARIANT),
            'color' => $this->colors_cache[$color_option_id] ?? '',
            'department' => $this->departments_cache[$department_option_id] ?? '',
            'price' => $product->getData(SouthbayProduct::ENTITY_PRICE),
            'price_retail' => $product->getData(SouthbayProduct::ENTITY_PRICE_RETAIL)
        ];
    }
}
