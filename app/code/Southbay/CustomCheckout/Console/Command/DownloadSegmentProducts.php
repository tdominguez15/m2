<?php

namespace Southbay\CustomCheckout\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\App\Filesystem\DirectoryList;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;


class DownloadSegmentProducts extends Command
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

    protected $state;

    protected $resourceConnection;

    protected $productCollectionFactory;

    protected $productRepository;

    protected $searchCriteriaBuilder;
    public function __construct(
        FileFactory                                          $fileFactory,
        \Psr\Log\LoggerInterface                             $log,
        \Southbay\Product\Helper\Data                        $southbay_helper,
        IoFile                                               $ioFile,
        DirectoryList                                        $directoryList,
        StoreManagerInterface                                $storeManager,
        State                                                $state,
        ResourceConnection                                   $resourceConnection,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->_log = $log;
        $this->_southbay_helper = $southbay_helper;
        $this->_fileFactory = $fileFactory;
        $this->_ioFile = $ioFile;
        $this->_directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->resourceConnection = $resourceConnection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:download:segments')
            ->setDescription('Import admins from predefined data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
     //   $items = $this->_session->getQuote()->getAllItems();
        $items = $this->getAllProducts();
        $list = $this->_southbay_helper->getMonthForDeliveryFromCurrent();

        $items_bu = [];


        foreach ($items as $item) {
        //    $output->writeln('<error>type: ' . print_r($item->getData()) . '</error>');

       //     $product = $item->getProduct();
            $product = $item;
           $southbay_department = $this->_southbay_helper->getProductValues($product->getSku(), ['southbay_department'])['southbay_department'];
      //     $southbay_department = $this->_southbay_helper->getProductValues($item->getSku(), ['southbay_department'])['southbay_department'];
           $output->writeln('<error>'. json_encode($southbay_department) .'</error>');
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
      //      $this->generateSheetByBU($sheet, $items, $list);
            try {
                $this->generateSheetByBU($sheet, $items, $list);
            } catch (\Exception $e) {
                $output->writeln('<error>Error al generar la hoja de cálculo: ' . $e->getMessage() . '</error>');
                $output->writeln('<error>Error al generar la hoja de cálculo: ' . json_encode($items) . '</error>');
            }

        }

        $base_path = 'export';

        $directoryPath = $this->_directoryList->getPath('var') . '/' . $base_path;

        if (!$this->_ioFile->fileExists($directoryPath, false)) {
            $this->_ioFile->mkdir($directoryPath, 0775);
        }

        $filename = 'cart-product-' . time() . '.xlsx';
        $filepath = $directoryPath . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        $output->writeln('<error>'. $filepath .'</error>');

        $this->_log->info('init download...');

      //  $response = $this->_fileFactory->create($filename, ['type' => 'filename', 'value' => $filepath, 'log' => $this->_log, 'rm' => true], 'var');
         $this->_fileFactory->create($filename, ['type' => 'filename', 'value' => $filepath, 'log' => $this->_log], 'var');

        return 1;
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

        foreach ($items as $item) {
            echo "product_id: " . $item->getId() . "\n";
            echo "product_type: " . $item->getTypeId() . "\n";
           // $product = $item->getProduct();
            $product = $item;
            $config = $this->_southbay_helper->getProductSeasonConfig($product);
            $parent = $this->_southbay_helper->findParentProduct($product->getId());
          //  $parent = $this->_southbay_helper->findParentProduct($item->getId());
           // $sizes = $this->_southbay_helper->getChildrenLabels($parent);
            $sizes = [];
            if(!empty($parent)){
           //     $parent =  $this->_southbay_helper->getProductVariants($item);
                $sizes = $this->_southbay_helper->getChildrenLabels($parent);
            }
            else {
                echo "___product_id: " . $item->getId() . "\n";
                echo "___product_type: " . $item->getTypeId() . "\n";
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
            print_r($list);
            foreach ($list as $season) {
                $rows[] = [
                    $season['label'],
                    0,
                    $parent->getSku(),
                    $product->getName(),
                    $southbay_purchase_unit,
                    $product->getPrice(),
                    $values['southbay_price_retail'],
                    $values['southbay_color'],
                    $values['southbay_department'],
                    $values['southbay_age'],
                    $values['southbay_gender'],
                    $values['southbay_sport'],
                    $values['southbay_silueta_1'],
                    $values['southbay_silueta_2']
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

        $activeWorksheet->freezePane('H2');

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

    public function getSeasonColumnsStartAt()
    {
        return count(self::getColumns());
    }
    public function getAllProducts()
    {
//        $productCollection = $this->productCollectionFactory->create();
//
//        $productCollection->addAttributeToSelect('*');
//
//        return $productCollection;



//        $searchCriteria = $this->searchCriteriaBuilder
//            ->addFilter('status', 1)
//            ->create();
//        $productList = $this->productRepository->getList($searchCriteria);







//        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//        $productCollectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
//
//        $productCollection = $productCollectionFactory->create();
//        $productCollection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
//       // $productCollection->addAttributeToFilter('type_id', 'configurable');
//
//        $productCollection->load();
//
//        return $productCollection->getItems();





        $configurableProducts = [];

        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('type_id', 'configurable')
                ->create();

            $productList = $this->productRepository->getList($searchCriteria);

            foreach ($productList->getItems() as $product) {
                $configurableProducts[] = $product;
            }
        } catch (\Exception $e) {

        }

        return $configurableProducts;



    }

}
