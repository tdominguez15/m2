<?php

namespace Southbay\Product\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;
use Southbay\Product\Cron\SouthbayProductImportCron;

class UploadAtOnce extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $fileUploader;
    private $filesystem;

    private $log;

    private $productImportHistoryFactory;
    private $repository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                            $context,
        PageFactory                                                        $resultPageFactory,
        UploaderFactory                                                    $fileUploader,
        \Psr\Log\LoggerInterface                                           $log,
        Filesystem                                                         $filesystem,
        \Southbay\Product\Model\SouthbayProductImportHistoryFactory        $productImportHistoryFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory $repository
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filesystem = $filesystem;
        $this->fileUploader = $fileUploader;
        $this->log = $log;
        $this->repository = $repository;
        $this->productImportHistoryFactory = $productImportHistoryFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $file = $this->uploadFile();

        if (!$file) {
            $this->messageManager->addErrorMessage(__('No fue posible subir el archivo'));
        } else {
            $params = $this->getRequest()->getParams();
            $store_id = $params['fields']['store_id'];
            $start_on_line_number = $params['fields']['start_on_line_number'];

            /**
             * @var \Southbay\Product\Model\SouthbayProductImportHistory $field
             */
            $field = $this->productImportHistoryFactory->create();
            $field->setSeasonId(0);
            $field->setStoreId($store_id);
            $field->setStatus(SouthbayProductImportHistoryInterface::STATUS_INIT);
            $field->setFile(basename($file));
            $field->setResultMsg(__('Esperando para comenzar a leer el archivo'));
            $field->setStartOnLineNumber($start_on_line_number);
            $field->setIsAtOnce(true);
            $field->setLines(0);

            $this->repository->save($field);

            $this->messageManager->addSuccessMessage(__('Linea subida'));
        }

        return $resultRedirect->setPath('*/*/atonce');
    }

    private function uploadFile()
    {
        try {
            $media_folder = SouthbayProductImportCron::UPLOAD_PATH;
            $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $target = $mediaDirectory->getAbsolutePath($media_folder);
            $uploader = $this->fileUploader->create(['fileId' => 'fields[southbay_products_file]']);
            $uploader->setAllowedExtensions(['xlsx']);
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            return $target . '/' . $result['file'];
        } catch (\Exception $e) {
            $this->log->error('Error uploading file', ['e' => $e]);
            return false;
        }
    }

    public function _isAllowed()
    {
        return true;
    }
}
