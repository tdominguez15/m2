<?php

namespace Southbay\Product\Controller\Adminhtml\Image;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Api\Data\SouthbayProductImportImgHistoryInterface;
use Southbay\Product\Cron\SouthbayProductImportImgCron;

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
        Context                                                               $context,
        PageFactory                                                           $resultPageFactory,
        UploaderFactory                                                       $fileUploader,
        \Psr\Log\LoggerInterface                                              $log,
        Filesystem                                                            $filesystem,
        \Southbay\Product\Model\SouthbayProductImportImgHistoryFactory        $productImportHistoryFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory $repository
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

        $params = $this->getRequest()->getParams();
        $name = $params['fields']['name'];
        $name = trim($name);

        $file = $this->uploadFile($name);

        if (!$file) {
            $this->messageManager->addErrorMessage(__('No fue posible subir el archivo'));
        } else {
            /**
             * @var \Southbay\Product\Model\SouthbayProductImportImgHistory $field
             */
            $field = $this->productImportHistoryFactory->create();
            $field->setName($name);
            $field->setStatus(SouthbayProductImportImgHistoryInterface::STATUS_INIT);
            $field->setFile(basename($file));
            $field->setResultMsg(__('Esperando para descomprimir el paquete de imagenes'));
            $field->setTotalFiles(0);

            $this->repository->save($field);

            $this->messageManager->addSuccessMessage(__('Paquete de imagenes subido'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    private function uploadFile($name)
    {
        try {
            $media_folder = SouthbayProductImportImgCron::UPLOAD_PATH . '/' . $name;
            $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $target = $mediaDirectory->getAbsolutePath($media_folder);
            $uploader = $this->fileUploader->create(['fileId' => 'fields[southbay_img_file]']);
            $uploader->setAllowedExtensions(['zip']);
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
