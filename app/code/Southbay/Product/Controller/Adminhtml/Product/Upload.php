<?php

namespace Southbay\Product\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;
use Southbay\Product\Cron\SouthbayProductImportCron;

class Upload extends Action
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

    protected function getType()
    {
        return SouthbayProductImportHistoryInterface::TYPE_IMPORT;
    }

    protected function getSuccessMessage()
    {
        return __('Linea subida');
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $file = $this->uploadFile();

        if (!$file) {
            $this->messageManager->addErrorMessage(__('No fue posible subir el archivo'));
        } else {
            $params = $this->getRequest()->getParams();
            $season_id = $params['fields']['season_id'] ?? 0;
            $start_on_line_number = $params['fields']['start_on_line_number'];
            $type_operation = $params['fields']['type_operation'];

            /**
             * @var \Southbay\Product\Model\SouthbayProductImportHistory $field
             */
            $field = $this->productImportHistoryFactory->create();
            $field->setSeasonId($season_id);
            $field->setStatus(SouthbayProductImportHistoryInterface::STATUS_INIT);
            $field->setFile(basename($file));
            $field->setResultMsg(__('Esperando para comenzar a leer el archivo'));
            $field->setStartOnLineNumber($start_on_line_number);
            $field->setLines(0);
            $field->setType($this->getType());
            $field->setTypeOperation($type_operation);

            $this->loadData($field, $params);

            $this->repository->save($field);

            $this->messageManager->addSuccessMessage($this->getSuccessMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function loadData(\Southbay\Product\Model\SouthbayProductImportHistory $field, $params)
    {
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
