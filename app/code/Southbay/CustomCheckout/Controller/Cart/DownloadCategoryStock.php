<?php

namespace Southbay\CustomCheckout\Controller\Cart;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Store\Model\StoreManagerInterface;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\ViewModel\SoldToViewModel;
use Southbay\Product\Helper\Data;
use Southbay\Product\Helper\SegmentFilter;
use Southbay\Product\Model\ProductExclusionRepository;

/**
 * Class DownloadCategoryStock
 * @package Southbay\CustomCheckout\Controller\Cart
 */
class DownloadCategoryStock extends Action
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var IoFile
     */
    protected $ioFile;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var SegmentFilter
     */
    protected $segmentFilter;

    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var Data
     */
    protected $southbayHelper;

    /**
     * @var ConfigStoreRepository
     */
    protected $configStoreRepository;

    /**
     * @var SoldToViewModel
     */
    protected $soldToViewModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductExclusionRepository
     */
    protected $productExclusionRepository;

    private $log;

    /**
     * DownloadCategoryStock constructor.
     * @param Context $context
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param StockStateInterface $stockState
     * @param FileFactory $fileFactory
     * @param IoFile $ioFile
     * @param DirectoryList $directoryList
     * @param SegmentFilter $segmentFilter
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param Data $southbayHelper
     * @param ConfigStoreRepository $configStoreRepository
     * @param SoldToViewModel $soldToViewModel
     * @param StoreManagerInterface $storeManager
     * @param ProductExclusionRepository $productExclusionRepository
     */
    public function __construct(
        Context                     $context,
        ProductCollectionFactory    $productCollectionFactory,
        CategoryFactory             $categoryFactory,
        StockStateInterface         $stockState,
        FileFactory                 $fileFactory,
        IoFile                      $ioFile,
        DirectoryList               $directoryList,
        SegmentFilter               $segmentFilter,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        Data                        $southbayHelper,
        ConfigStoreRepository       $configStoreRepository,
        SoldToViewModel             $soldToViewModel,
        StoreManagerInterface       $storeManager,
        ProductExclusionRepository  $productExclusionRepository,
        \Psr\Log\LoggerInterface    $log
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->stockState = $stockState;
        $this->fileFactory = $fileFactory;
        $this->ioFile = $ioFile;
        $this->directoryList = $directoryList;
        $this->segmentFilter = $segmentFilter;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->southbayHelper = $southbayHelper;
        $this->configStoreRepository = $configStoreRepository;
        $this->soldToViewModel = $soldToViewModel;
        $this->storeManager = $storeManager;
        $this->productExclusionRepository = $productExclusionRepository;
        $this->log = $log;
        parent::__construct($context);
    }

    /**
     * Execute method to download category stock.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $category = $this->categoryFactory->create()->load($categoryId);
        $storeId = $this->storeManager->getStore()->getId();

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addCategoryFilter($category)
            ->addAttributeToSelect(['name', 'sku', 'southbay_channel_level_list'])
            ->setStoreId($storeId)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('type_id', 'configurable')
            ->addAttributeToFilter('visibility', ['in' => [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
            ]]);


        $excludedProductIds = $this->productExclusionRepository->getExcludedProductIds($storeId);

        if ($excludedProductIds && count($excludedProductIds) > 0) {
            $productCollection->addFieldToFilter('entity_id', ['nin' => $excludedProductIds]);
        }
        if ($productCollection->getSize() == 0) {
            $this->messageManager->addErrorMessage(__('No products found in this category.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'Product Name');
        $sheet->setCellValue('B1', 'SKU');
        $sheet->setCellValue('C1', 'Talle');
        $sheet->setCellValue('D1', 'Stock');

        // Estilos de encabezados
        $headerStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4CAF50'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyleArray);

        // Ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);

        $row = 2;
        $storeId = $this->storeManager->getStore()->getId();
        $config = $this->configStoreRepository->findByStoreId($storeId);
        $country = $config->getSouthbayCountryCode();
        $segmentation = $this->getSegmentations($config);

        foreach ($productCollection as $product) {
            try {
                // $this->log->debug('getting product', ['sku' => $product->getSku(), 'product_id' => $product->getId()]);
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                $sizesLabels = $this->southbayHelper->getChildrenLabels($product);
                $arrayProductSegmentation = [];

                if ($config->getSouthbayFunctionCode() != ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
                    if (!empty($segmentation)) {
                        $productSegmentation = $product->getSouthbayChannelLevelList();
                        if (!empty($productSegmentation)) {
                            $arrayProductSegmentation = explode(';', $productSegmentation);
                        }
                    }

                    $match = false;
                    if (!empty($segmentation) && !empty($arrayProductSegmentation)) {
                        foreach ($segmentation as $segment) {
                            if (in_array($country . ":" . $segment, $arrayProductSegmentation)) {
                                $match = true;
                            }
                        }
                    }
                    if (!$match && !empty($segmentation)) {
                        break;
                    }
                }

                foreach ($childProducts as $childProduct) {
                    if ($childProduct->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                        continue;
                    }

                    $stock = $this->southbayHelper->getStockByProduct($childProduct, $storeId)['qty'];
                    $size = $childProduct->getData('southbay_size');
                    foreach ($sizesLabels as $sizesLabel) {
                        if ($size == $sizesLabel['value']) {
                            $size = $sizesLabel['label'];
                            break;
                        }
                    }
                    $sheet->setCellValue('A' . $row, $product->getName());
                    $sheet->setCellValue('B' . $row, $product->getSku());
                    $sheet->setCellValue('C' . $row, $size);
                    $sheet->setCellValue('D' . $row, $stock);
                    $row++;
                }
            } catch (\Exception $e) {
                $this->log->error('Error getting product:', ['sku' => $product->getSku(), 'id' => $product->getId(), 'e' => $e]);
                throw $e;
            }
        }

        $base_path = '/export';

        $directoryPath = $this->directoryList->getPath('var') . $base_path;
        if (!$this->ioFile->fileExists($directoryPath, false)) {
            $this->ioFile->mkdir($directoryPath, 0775);
        }

        $filename = 'stock.xlsx';
        $filepath = $directoryPath . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        $response = $this->fileFactory->create($filename, ['type' => 'filename', 'value' => $filepath, 'rm' => true], 'var');

        return $response;
    }

    /**
     * Get segmentations based on config.
     *
     * @param $config
     * @return array
     * @throws \Exception
     */
    public function getSegmentations($config)
    {
        $soldTo = $this->soldToViewModel->getSoldToFromSession();
        if (is_null($soldTo)) {
            throw new \Exception('Invalid request. Sold to not found');
        }

        if (is_null($config)) {
            throw new \Exception('Store not configured');
        }

        $segmentations = $soldTo->getSegmentation();

        if (empty($segmentations)) {
            $segmentations = ['n/a'];
        } elseif ($segmentations == '*all-for-southbay*') {
            return [];
        }
        return explode(',', $segmentations);
    }
}

?>
