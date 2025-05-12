<?php

namespace Southbay\CustomCheckout\Controller\Cart;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;

use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;

class Download implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_context;

    protected $_log;

    protected $_session;

    protected $_checkoutCart;

    protected $_resultRedirectFactory;

    protected $_customerSession;

    protected $_messageManager;

    protected $_southbay_helper;

    protected $_fileFactory;

    protected $_ioFile;

    protected $_directoryList;

    private $storeManager;

    protected $imageHelper;

    protected $filesystem;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        CheckoutSession                                      $session,
        CustomerSession                                      $customerSession,
        Context                                              $context,
        PageFactory                                          $resultPageFactory,
        FileFactory                                          $fileFactory,
        Cart                                                 $checkoutCart,
        \Magento\Framework\Message\ManagerInterface          $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Psr\Log\LoggerInterface                             $log,
        \Southbay\Product\Helper\Data                        $southbay_helper,
        IoFile                                               $ioFile,
        DirectoryList                                        $directoryList,
        StoreManagerInterface                                $storeManager,
        ImageHelper                                          $imageHelper,
        Filesystem                                           $filesystem,
    )
    {
        $this->_context = $context;
        $this->_log = $log;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_checkoutCart = $checkoutCart;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_southbay_helper = $southbay_helper;
        $this->_fileFactory = $fileFactory;
        $this->_ioFile = $ioFile;
        $this->_directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->filesystem = $filesystem;
    }

    public static function getSeasonDateColumnTitle()
    {
        return __('Temporada');
    }

    public static function getColumns()
    {
        return [
            self::getSeasonDateColumnTitle(),
            __('Total'),
            __('Sku'),
            __('Nombre'),
            __('Foto'),
            __('Link'),
            __('Unit. Compra'),
            __('Precio'),
            __('Precio Sugerido'),
            __('Color'),
            __('Departamento'),
            __('Edad'),
            __('Genero'),
            __('Deporte'),
            __('Silueta'),
            __('Caracteristica')

        ];
    }

    public static function getSkuColumnIndex()
    {
        return 2;
    }

    public static function getTotalColumnIndex()
    {
        return 1;
    }

    public static function getSeasonColumnIndex()
    {
        return 0;
    }

    public static function getImagenColumnIndex()
    {
        return 'E';
    }
    public static function getImagenLinkColumnIndex()
    {
        return 'F';
    }

    public static function getSeasonColumnsStartAt()
    {
        return count(self::getColumns());
    }

    public function execute()
    {
        $items = $this->_session->getQuote()->getAllItems();
        $list = $this->_southbay_helper->getMonthForDeliveryFromCurrent();

        $items_bu = [];

        foreach ($items as $item) {
            $product = $item->getProduct();
            $southbay_department = $this->_southbay_helper->getProductValues($product->getSku(), ['southbay_department'])['southbay_department'];
            if (!isset($items_bu[$southbay_department])) {
                $items_bu[$southbay_department] = [];
            }
            $items_bu[$southbay_department][] = $item;
        }

        $spreadsheet = new Spreadsheet();
        $first = true;

        foreach ($items_bu as $items) {
            if ($first) {
                $first = false;
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $this->generateSheetByBU($sheet, $items, $list);
        }

        $base_path = 'export';

        $directoryPath = $this->_directoryList->getPath('var') . '/' . $base_path;

        if (!$this->_ioFile->fileExists($directoryPath, false)) {
            $this->_ioFile->mkdir($directoryPath, 0775);
        }

        $filename = 'cart-product.xlsx';
        $filepath = $directoryPath . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        $this->_log->info('init download...');

        $response = $this->_fileFactory->create($filename, ['type' => 'filename', 'value' => $filepath, 'log' => $this->_log, 'rm' => true], 'var');

        return $response;
    }

    public function generateSheetByBU($activeWorksheet, $items, $list)
    {
        $all_sizes_map = [];
        $all_sizes = [];
        $columns = self::getColumns();

        $total_start_columns = self::getSeasonColumnsStartAt();

        $rows_values = [];
        $rows = [];
        $row_season = [];
        $first = true;
        $currentDate = new \DateTime();
        $productReleaseDate = null;
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaBaseDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $config = $this->_southbay_helper->getProductSeasonConfig($product);
            $parent = $this->_southbay_helper->findParentProduct($product->getId());
            $sizes = $this->_southbay_helper->getChildrenLabels($parent);

            $productReleaseDate = $parent->getData('southbay_release_date');
            if($productReleaseDate !== null) {
                $productReleaseDate = new \DateTime($productReleaseDate);
            }


            foreach ($sizes as $size) {
                if (!isset($all_sizes_map[$size['value']])) {
                    $all_sizes_map[$size['value']] = $size;
                    $all_sizes[] = $size;
                }
            }

            $values = $this->_southbay_helper->getProductValues($product->getSku(),
                [
                    'southbay_color',
                    'southbay_department',
                    'southbay_age',
                    'southbay_gender',
                    'southbay_sport',
                    'southbay_silueta_1',
                    'southbay_silueta_2',
                    'southbay_purchase_unit',
                    'southbay_price_retail'
                ]
            );

            if (is_null($parent)) {
                $parent = $product;
            }

            if ($first) {
                $first = false;
                $activeWorksheet->setTitle($values['southbay_department']);
            }

            $southbay_purchase_unit = $values['southbay_purchase_unit'];

            if (empty($southbay_purchase_unit)) {
                $southbay_purchase_unit = 1;
            }

            $baseImageUrl = $this->imageHelper->init($parent, 'product_base_image')->getUrl();


            foreach ($list as $season) {
                    if ($productReleaseDate !== null) {
                        $seasonMonthDate = new \DateTime($season['date']);
                        if ($this->_southbay_helper->setDayToFirst($productReleaseDate) > $this->_southbay_helper->setDayToFirst($seasonMonthDate)) {
                            continue;
                        }
                    }
                $rows[] = [
                    $season['label'],
                    0,
                    $parent->getSku(),
                    $product->getName(),
                    '',
                    $baseImageUrl,
                    $southbay_purchase_unit,
                    $product->getPrice(),
                    $values['southbay_price_retail'],
                    $values['southbay_color'],
                    $values['southbay_department'],
                    $values['southbay_age'],
                    $values['southbay_gender'],
                    $values['southbay_sport'],
                    $values['southbay_silueta_1'],
                    $values['southbay_silueta_2'],

                ];

                $row_season[] = $season['code'];

                foreach ($sizes as $size) {
                    $key = $parent->getSku() . '-' . $size['value'] . '-' . $season['code'];
                    if (isset($config[$season['code']][$size['value']])) {
                        $rows_values[$key] = $config[$season['code']][$size['value']];
                    } else {
                        $rows_values[$key] = 0;
                    }
                }
            }
        }

        $all_sizes = $this->_southbay_helper->sortSizesItems($all_sizes);

        foreach ($all_sizes as $size) {
            $columns[] = $size['label'];

            foreach ($rows as $index => $row) {
                $sku = $row[self::getSkuColumnIndex()];
                $season = $row_season[$index];
                $key = $sku . '-' . $size['value'] . '-' . $season;

                if (isset($rows_values[$key])) {
                    $row[] = strval($rows_values[$key]);
                } else {
                    $row[] = '';
                }
                $rows[$index] = $row;
            }
        }

        array_unshift($rows, $columns);

        $activeWorksheet->fromArray($rows);

        $highestRow = $activeWorksheet->getHighestRow();
        $imageColumn = self::getImagenColumnIndex();
        $linkColumn = self::getImagenLinkColumnIndex();
        for ($row = 2; $row <= $highestRow; $row++) {
            $cell = $activeWorksheet->getCell($linkColumn . $row);
            $linkUrl = $cell->getValue();



            $urlParts = explode('?', $linkUrl);
            $imageUrlClean = $urlParts[0];

            $imagePath = str_replace($mediaBaseUrl, $mediaBaseDir, $imageUrlClean);
            $imagePath = str_replace('__BASE__', '__EXCEL__',$imagePath);
            if (file_exists($imagePath)) {
                $cell->getHyperlink()->setUrl($cell->getValue());
                $cell->setValue('Zoom Imagen');




                $activeWorksheet->getRowDimension($row)->setRowHeight(40);
                $activeWorksheet->getColumnDimension($imageColumn)->setWidth(8);


                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Product Image');
                $drawing->setDescription('Product Image');
                $drawing->setPath($imagePath);
                $drawing->setHeight(40);
                $drawing->setWidth(40);
                $drawing->setCoordinates($imageColumn . $row);
                $drawing->setOffsetX(4);
                $drawing->setOffsetY(4);
                $drawing->setWorksheet($activeWorksheet);
            }
            else   $cell->setValue('');
        }

        $rindex = 0;
        $season_num = 0;

        $first_season_column = '';
        $last_season_column = '';
        $total_column = '';

        for ($i = 0; $i < count($rows); $i++) {
            $rindex++;

            $cindex = '';
            for ($j = 0; $j < count($columns); $j++) {
                if ($cindex === '') {
                    $cindex = 'A';
                } else {
                    $cindex++;
                }

                $style = $activeWorksheet->getStyle($cindex . $rindex);
                $style->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                if (in_array($cindex, ['F'])) {
                    $style->getNumberFormat()->setFormatCode('"$"#,##0.00');
                }
                if (in_array($cindex, ['G'])) {
                    $style->getNumberFormat()->setFormatCode('"$"#,##0');
                }

                if ($rindex == 1) {
                    $fill = $style->getFill();
                    $fill->setFillType(Fill::FILL_SOLID);
                    $fill->getStartColor()->setRGB('CCCCCC');
                    $style->getFont()->setBold(true);

                    if ($j === self::getTotalColumnIndex()) {
                        $total_column = $cindex;
                    }

                    if ($j >= $total_start_columns) {
                        if ($first_season_column == '') {
                            $first_season_column = strval($cindex);
                            $last_season_column = $first_season_column;
                        } else {
                            $last_season_column = strval($cindex);
                        }
                    }
                } else {
                    $apply_color = -1;

                    if ($j === self::getTotalColumnIndex() || $j == self::getSeasonColumnIndex()) {
                        $apply_color = 1;
                    } else if ($j >= $total_start_columns) {
                        $value = $activeWorksheet->getCell($cindex . $rindex)->getValue();

                        if (!is_null($value)) {
                            $apply_color = 1;
                        } else {
                            $apply_color = 0;
                        }
                    }

                    if ($apply_color == 1 || $apply_color == 0) {
                        $fill = $style->getFill();
                        $fill->setFillType(Fill::FILL_SOLID);

                        if ($apply_color == 1) {
                            if ($season_num == 0) {
                                $fill->getStartColor()->setRGB('C6E0B4');
                            } else if ($season_num == 1) {
                                $fill->getStartColor()->setRGB('F8CBAD');
                            } else {
                                $fill->getStartColor()->setRGB('FFD966');
                            }
                        } else if ($apply_color == 0) {
                            $fill->getStartColor()->setRGB('E4E4E4');
                            $style->getFont()->getColor()->setRGB('E4E4E4');

                        }
                    }
                }
            }
            if ($rindex > 1) {
                $season_num++;
                if ($season_num > 2) {
                    $season_num = 0;
                }
            }
        }

        $rindex = 0;
        for ($i = 0; $i < count($rows); $i++) {
            $rindex++;
            if ($rindex > 1) {
                $range = $first_season_column . strval($rindex) . ':' . $last_season_column . strval($rindex);
                $activeWorksheet->setCellValue($total_column . strval($rindex), '=SUM(' . $range . ')');
            }
        }
        $this->SetTotalQtyCell($activeWorksheet, $rows);


    }

    public function SetTotalQtyCell($activeWorksheet, $rows)
    {
        $startIndex = 2;
        $endIndex = count($rows);
        $range = 'B' . $startIndex . ':B' . $endIndex;
        $formula = '=SUM(' . $range . ')';
        $formulaCell = 'B' . strval($endIndex + 1);
        $labelCell = 'A' . strval($endIndex + 1);
        $activeWorksheet->setCellValue($formulaCell, $formula);
        $activeWorksheet->setCellValue($labelCell, 'Total');
        $activeWorksheet->getStyle($labelCell)->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E4E4E4']
            ]
        ]);
        $activeWorksheet->getStyle($formulaCell)->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E4E4E4']
            ]
        ]);
        $activeWorksheet->freezePane('H2');
    }
}
