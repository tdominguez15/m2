<?php

namespace Southbay\CustomCheckout\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Southbay\CustomCheckout\Controller\Cart\Download;
use Southbay\Product\Api\Data\SouthbayProduct;
use Southbay\Product\Model\ResourceModel\SeasonRepository;

class UploadCardData extends AbstractHelper
{
    private $messageManager;
    private $resultRedirectFactory;
    private $southbay_helper;
    private $filesystem;
    private $productRepository;
    private $seasonRepository;

    private $collectionFactory;

    private $cache;

    public function __construct(
        \Magento\Framework\App\Helper\Context                          $context,
        \Magento\Framework\Message\ManagerInterface                    $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory           $resultRedirectFactory,
        \Southbay\Product\Helper\Data                                  $southbay_helper,
        Filesystem                                                     $filesystem,
        SeasonRepository                                               $seasonRepository,
        ProductRepository                                              $productRepository,
        \Magento\Framework\App\CacheInterface                          $cache,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    )
    {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->southbay_helper = $southbay_helper;
        $this->filesystem = $filesystem;
        $this->productRepository = $productRepository;
        $this->seasonRepository = $seasonRepository;
        $this->collectionFactory = $collectionFactory;
        $this->cache = $cache;
    }

    public function getCardFromFile($file, $store, $form_key = '')
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $total_sheets = $spreadsheet->getSheetCount();
        $seasons = $this->southbay_helper->getMonthForDeliveryFromCurrent($store);
        $map_seasons = [];

        foreach ($seasons as $season) {
            $map_seasons[$season['label']] = $season['code'];
        }

        $current_cart_items = [];

        for ($i = 0; $i < $total_sheets; $i++) {
            $spreadsheet->setActiveSheetIndex($i);
            $result = $this->readSheet($spreadsheet);

            foreach ($result['rows'] as $row) {
                $current_cart_items = $this->getCardItem($row, $result['sizes'], $result['start_size_columns_at'], $current_cart_items, $map_seasons, $store, $form_key);
            }
        }

        return $current_cart_items;
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @return array
     */
    public function readSheet(Spreadsheet $spreadsheet)
    {
        $stop = false;
        $sheet = $spreadsheet->getActiveSheet();
        $max_column_number = 0;
        $start_size_columns_at = 0;
        $total_start_columns = 0;
        $columns = [];
        $sizes = [];
        $rows = [];

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            $column_index = -1;
            $_row = [];
            foreach ($row->getCellIterator() as $cell) {
                $column_index++;
                $value = $cell->getValue();

                if (empty($value) && $column_index === 0) {
                    $stop = true;
                    break;
                }

                if ($rowIndex == 1) {
                    if (empty($value)) {
                        break;
                    } else {
                        $max_column_number++;
                        $columns[] = strval($value);
                        if ($column_index >= Download::getSeasonColumnsStartAt()) {
                            if ($start_size_columns_at == 0) {
                                $start_size_columns_at = $column_index;
                            }
                            $sizes[] = strval($value);
                        }
                    }
                } else {
                    if ($column_index < $max_column_number) {
                        $_row[] = $value;
                    } else {
                        break;
                    }
                }
            }
            if ($stop) {
                break;
            } else if ($rowIndex > 1) {
                $rows[] = $_row;
            }
        }

        return [
            'rows' => $rows,
            'columns' => $columns,
            'sizes' => $sizes,
            'total_start_columns' => $total_start_columns,
            'start_size_columns_at' => $start_size_columns_at,
            'total_columns' => $max_column_number
        ];
    }

    public function getCardItem($row, $sizes, $start_size_columns_at, $current_cart_items, $map_seasons, $store, $form_key)
    {
        // $this->_logger->debug('addToCart...', ['row' => $row, 'sizes' => $sizes, 'start_size_columns_at' => $start_size_columns_at]);
        $sku = $row[Download::getSkuColumnIndex()];

        if (empty($sku) || empty(trim($sku))) {
            return $current_cart_items;
        }

        $season_label = $row[Download::getSeasonColumnIndex()];

        if (empty($season_label) || empty(trim($season_label))) {
            return $current_cart_items;
        }

        $season_label = trim($season_label);

        if (!isset($map_seasons[$season_label])) {
            // $this->_logger->debug('Temporada invalida', ['sku' => $sku, 'season_label' => $season_label, 'row' => $row]);
            return $current_cart_items;
        }

        $season = $map_seasons[$season_label];

        if (isset($current_cart_items[$sku])) {
            $_cart_item = $current_cart_items[$sku];
        } else {
            $product = $this->findProductBySku($sku, $store->getId());

            if (is_null($product)) {
                return $current_cart_items;
            }

            $productReleaseDate = $product->getData('southbay_release_date');
            if ($productReleaseDate !== null) {
                $productReleaseDate = new \DateTime($productReleaseDate);
            }
            if ($productReleaseDate !== null) {
                $seasonMonthDate = $this->convertSeasonLabelToDate($season_label, $store);

                if (is_null($seasonMonthDate) || $this->southbay_helper->setDayToFirst($productReleaseDate) > $this->southbay_helper->setDayToFirst($seasonMonthDate)) {
                    return $current_cart_items;
                }
            }

            $product_sizes = $this->southbay_helper->getChildrenLabels($product);
            $first_product = $this->southbay_helper->getFirstProductVariant($product);

            $_cart_item = [
                'sizes' => $product_sizes,
                'product' => $product,
                'southbay_release_date' => $productReleaseDate ? $productReleaseDate->format('Y-m') : null,
                'first_product_variant' => $first_product,
                'request' => ['form_key' => $form_key]
            ];
        }

        $product_sizes = $_cart_item['sizes'];
        $request = $_cart_item['request'];
        $data = [];
        $qty = 0;

        foreach ($product_sizes as $product_size) {
            $found = false;

            foreach ($sizes as $index => $size) {
                if ($product_size['label'] == $size) {
                    $found = true;
                    $value = $row[$index + $start_size_columns_at];
                    $data[$product_size['value']] = intval($value);
                    break;
                }
            }

            if (!$found) {
                $data[$product_size['value']] = 0;
            }

            $qty += $data[$product_size['value']];
        }

        $request[$season] = $data;
        $request['qty'] = $qty;
        $_cart_item['request'] = $request;

        if (isset($current_cart_items[$sku])) {
            $current_cart_items[$sku]['request']['qty'] += $qty;
            $current_cart_items[$sku]['request'][$season] = $data;
        } else {
            $current_cart_items[$sku] = $_cart_item;
        }

        return $current_cart_items;
    }

    public function findProductBySku($sku, $store_id)
    {
        $product = null;
        try {
            // $product = $this->productRepository->get($sku, false, $store->getId());
            $collection = $this->collectionFactory->create();
            $collection->setStoreId($store_id);
            $collection->addAttributeToSelect(['sku', 'type_id', 'name', 'visibility', 'price', 'southbay_price_retail', 'southbay_release_date',
                SouthbayProduct::ENTITY_COLOR, SouthbayProduct::ENTITY_DEPARTMENT, SouthbayProduct::ENTITY_SKU_VARIANT]);
            $collection->addFieldToFilter('sku', ['eq' => $sku]);
            $product = $collection->getFirstItem();
        } catch (NoSuchEntityException $e) {
            // $this->_logger->error('No se encontro uno los articulo', ['sku' => $sku, 'row' => $row]);
        }

        return $product;
    }

    //convertir label a objeto de fecha
    public function convertSeasonLabelToDate(string $season_label, $store): \DateTime|null
    {
        /*
        list($monthLabel, $year) = explode('/', $season_label);

        $_result = $this->seasonRepository->parseMonthLabelToDate($season_label, $this->checkoutCart->getQuote()->getStore());

        $this->log->debug('convertSeasonLabelToDate', ['monthLabel' => $monthLabel, 'year' => $year, 'r' => date('Y-m-d', $_result)]);

        $month = date('m', strtotime($monthLabel));
        $year = '20' . $year;
        $formattedDate = "$year-$month-01";
        return new \DateTime($formattedDate);
        */

        $result = $this->seasonRepository->parseMonthLabelToDate($season_label, $store);

        if (!$result) {
            return null;
        }

        $date = new \DateTime();
        $date->setTimestamp($result);

        return $date;
    }
}
