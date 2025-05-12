<?php

namespace Southbay\Product\Controller\Adminhtml\ReportAttribute;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;

class Generate extends Action implements HttpPostActionInterface
{
    protected $collectionFactory;
    protected $request;
    protected $logger;
    protected $storeManager;
    protected $fileFactory;
    protected $directoryList;
    protected $jsonFactory;

    public function __construct(
        Action\Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        FileFactory $fileFactory,
        DirectoryList $directoryList,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $storeId = $this->request->getParam('fields')['store_id'] ?? null;

        if (!$storeId) {
            $this->messageManager->addErrorMessage(__('Store ID is required.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
        }

        try {
            $productCollection = $this->getProductCollection($storeId);

            if ($productCollection->getSize() > 0) {
                $fileName = 'report_products_' . date('Y-m-d_H-i-s') . '.csv';

                $filePath = $this->generateCsv($productCollection, $fileName);
                $content = file_get_contents($filePath);
                unlink($filePath);

                $result = $this->jsonFactory->create();
                return  $result->setData([
                    'result' => $content,
                    'error' => false
                ]);
            } else {
                $this->messageManager->addErrorMessage(__('No se encontraron productos que reportar.'));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
            }
        } catch (\Exception $e) {
            $this->logger->error('ocurrio un error generando el reporte: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('ocurrio un error generando el reporte.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
        }
    }

    /**
     * Obtener la colección de productos con los filtros necesarios
     *
     * @param int $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection($storeId)
    {
        $productCollection = $this->collectionFactory->create();
        $productCollection->addStoreFilter($storeId)
            ->addFieldToFilter('type_id', 'configurable')
            ->addFieldToFilter(
                [
                    ['attribute' => 'southbay_channel_level_list', 'null' => true],
                    ['attribute' => 'southbay_purchase_unit', 'eq' => 1]
                ],
                ['logic' => 'or']
            );

        return $productCollection;
    }

    /**
     * Generar el archivo CSV con los datos de la colección de productos
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param string $fileName
     * @return string $filePath
     */
    private function generateCsv($productCollection, $fileName)
    {

        $basePath = 'export/attribute';
        $directoryPath = $this->directoryList->getPath('var') . '/' . $basePath;

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }

        $filePath = $directoryPath . '/' . $fileName;
        $file = fopen($filePath, 'w');
        fputcsv($file, ['SKU', 'Southbay Purchase Unit', 'Southbay Channel Level']);

        foreach ($productCollection as $product) {
            fputcsv($file, [
                $product->getSku(),
                $product->getData('southbay_purchase_unit') ?? '',
                $product->getData('southbay_channel_level_list') ?? ''
            ]);
        }
        fclose($file);

        return $filePath;
    }
}
