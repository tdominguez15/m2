<?php

namespace Southbay\CustomCheckout\Controller\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Magento\Catalog\Model\ProductRepository;
use Southbay\Product\Model\ResourceModel\SeasonRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Upload implements HttpPostActionInterface
{
    private $checkoutCart;
    private $messageManager;
    private $resultRedirectFactory;
    private $log;
    private $southbay_helper;
    private $context;
    private $fileUploader;
    private $filesystem;
    private $sectionPool;
    private $productRepository;
    private $seasonRepository;
    private $scopeConfig;
    const XML_PATH_ENABLE_LOGGING = 'log_administrator/cart_excel_upload/enable_logging';

    public function __construct(
        Context                                              $context,
        Cart                                                 $checkoutCart,
        \Magento\Framework\Message\ManagerInterface          $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Psr\Log\LoggerInterface                             $log,
        \Southbay\Product\Helper\Data                        $southbay_helper,
        UploaderFactory                                      $fileUploader,
        Filesystem                                           $filesystem,
        SectionPoolInterface                                 $sectionPool,
        SeasonRepository                                     $seasonRepository,
        ProductRepository                                    $productRepository,
        ScopeConfigInterface                                 $scopeConfig
    )
    {
        $this->log = $log;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->checkoutCart = $checkoutCart;
        $this->context = $context;
        $this->southbay_helper = $southbay_helper;
        $this->fileUploader = $fileUploader;
        $this->filesystem = $filesystem;
        $this->sectionPool = $sectionPool;
        $this->productRepository = $productRepository;
        $this->seasonRepository = $seasonRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $file = $this->uploadFile();
        if (!$file) {
            $this->messageManager->addErrorMessage(__('No se ha cargado ningún archivo.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }
        $southbay_clean_cart_before = $this->context->getRequest()->getParam('southbay_clean_cart_before', false);

        $this->read($file, $southbay_clean_cart_before);

        $this->checkoutCart->getQuote()->setTotalsCollectedFlag(false);
        $this->checkoutCart->getQuote()->collectTotals();
        $this->checkoutCart->getQuote()->cleanModelCache();

        $this->sectionPool->getSectionsData(['cart', 'directory-data', 'messages'], true);

        $message = __('Carrito actualizado');
        $this->messageManager->addSuccessMessage($message);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart');

        return $resultRedirect;
    }

    private function uploadFile()
    {
        try {
            $media_folder = 'cart/import';
            $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $target = $mediaDirectory->getAbsolutePath($media_folder);
            $uploader = $this->fileUploader->create(['fileId' => 'southbay_cart_file']);
            $uploader->setAllowedExtensions(['xlsx']);
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            return $target . '/' . $result['file'];
        } catch (\Exception $e) {
            return false;
        }
    }

    private function read($file, $southbay_clean_cart_before)
    {
        $quote = $this->checkoutCart->getQuote();
        $quote->setExtShippingInfo(basename($file));
        $quote_items = $quote->getAllItems();
        $items_before_map = [];

        foreach ($quote_items as $item) {
            if ($southbay_clean_cart_before) {
                $this->checkoutCart->removeItem($item->getItemId());
            } else {
                $items_before_map[$item->getProduct()->getId()] = $item;
            }
        }

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $total_sheets = $spreadsheet->getSheetCount();
        $seasons = $this->southbay_helper->getMonthForDeliveryFromCurrent();
        $map_seasons = [];

        foreach ($seasons as $season) {
            $map_seasons[$season['label']] = $season['code'];
        }

        $current_cart_items = [];
        $log = $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_LOGGING);;

        for ($i = 0; $i < $total_sheets; $i++) {
            $spreadsheet->setActiveSheetIndex($i);
            $result = $this->readSheet($spreadsheet);

            foreach ($result['rows'] as $row) {
                if($log ){
                    $this->log->debug('addToCart...', ['row' => $row, 'sizes' => $result['sizes'], 'start_size_columns_at' => $result['start_size_columns_at']]) ;
                }
                $current_cart_items = $this->addToCart($row, $result['sizes'], $result['start_size_columns_at'], $current_cart_items, $map_seasons);
            }
        }

        foreach ($current_cart_items as $cart_item) {
            $request = $cart_item['request'];
            $product = $cart_item['first_product_variant'];

            if ($request['qty'] > 0) {
                $request['qty'] = strval($request['qty']);
                $request['from_import'] = true;
                $this->checkoutCart->addProduct($product, $request);
                //      $params = new DataObject(['qty' => $request['qty']]);
                //       $quote->addProduct($product,$params);
            } else if (isset($items_before_map[$product->getId()])) {
                $item = $items_before_map[$product->getId()];
                $this->checkoutCart->removeItem($item->getItemId());
            }
        }

        $this->checkoutCart->save();

        $quote_items = $quote->getAllItems();

        /**
         * @var \Magento\Quote\Model\Quote\Item $item
         */
        foreach ($quote_items as $item) {
            $product = $item->getProduct();
            $price = $product->getPrice();
            $subtotal = $price * $item->getQty();

            $item->setPrice($price);
            $item->setBasePrice($price);
            $item->setPriceInclTax($price);

            $item->setRowTotal($subtotal);
            $item->setBaseRowTotal($subtotal);

            $item->setRowTotalInclTax($subtotal);
            $item->setBaseRowTotalInclTax($subtotal);
        }

        $quote->save();
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @return array
     */
    private function readSheet(Spreadsheet $spreadsheet)
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

    private function addToCart($row, $sizes, $start_size_columns_at, $current_cart_items, $map_seasons)
    {


        $form_key = $this->context->getRequest()->getParam('form_key');
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
            $this->log->debug('Temporada invalida', ['sku' => $sku, 'season_label' => $season_label, 'row' => $row]);
            return $current_cart_items;
        }

        $season = $map_seasons[$season_label];

        if (isset($current_cart_items[$sku])) {
            $_cart_item = $current_cart_items[$sku];
        } else {
            $product = null;

            try {
                $product = $this->productRepository->get($sku);
            } catch (NoSuchEntityException $e) {
                $this->log->error('No se encontro uno los articulo', ['sku' => $sku, 'row' => $row]);
            }

            if (is_null($product)) {
                return $current_cart_items;
            }
            $productReleaseDate = $product->getData('southbay_release_date');
            if ($productReleaseDate !== null) {
                $productReleaseDate = new \DateTime($productReleaseDate);
            }
            if ($productReleaseDate !== null) {
                $seasonMonthDate = $this->convertSeasonLabelToDate($season_label);

                if (is_null($seasonMonthDate) || $this->southbay_helper->setDayToFirst($productReleaseDate) > $this->southbay_helper->setDayToFirst($seasonMonthDate)) {
                    return $current_cart_items;
                }
            }

            $product_sizes = $this->southbay_helper->getChildrenLabels($product);
            $first_product = $this->southbay_helper->getFirstProductVariant($product);

            $_cart_item = [
                'sizes' => $product_sizes,
                'product' => $product,
                'first_product_variant' => $first_product,
                'request' => ['form_key' => $form_key]
            ];
        }

        $product_sizes = $_cart_item['sizes'];
        $request = $_cart_item['request'];
        $data = [];
        $qty = 0;
        // $start_size_columns_index = $start_size_columns_at - 1;

        // $this->log->info('product sizes', ['product_sizes' => $product_sizes]);

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

    //convertir label a objeto de fecha
    public function convertSeasonLabelToDate(string $season_label): \DateTime|null
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

        $result = $this->seasonRepository->parseMonthLabelToDate($season_label, $this->checkoutCart->getQuote()->getStore());

        if (!$result) {
            return null;
        }

        $date = new \DateTime();
        $date->setTimestamp($result);

        return $date;
    }
}
