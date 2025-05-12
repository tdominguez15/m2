<?php

namespace Southbay\Product\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;
use Southbay\Product\Cron\SouthbayProductImportCron;

class Download extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;

    private $fileFactory;

    private $filesystem;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                                              $context,
        PageFactory                                                                          $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory                   $repository,
        FileFactory                                                                          $fileFactory,
        Filesystem                                                                           $filesystem
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $id = $params['id'];

        if (!$id) {
            $this->messageManager->addErrorMessage(__('No se puede buscar el registro que intenta descargar'));
        } else {
            /**
             * @var \Southbay\Product\Model\SouthbayProductImportHistory $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            $media_folder = SouthbayProductImportCron::UPLOAD_PATH;
            $filepath = $media_folder . '/' . $item->getFile();

            $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $target = $mediaDirectory->getAbsolutePath($media_folder);
            $file_to_download = $target . '/' . $item->getFile();

            if (!file_exists($file_to_download)) {
                $this->messageManager->addErrorMessage(__('El archivo no está disponible en el servidor'));
            } else {
                return $this->fileFactory->create($item->getFile(), ['type' => 'filename', 'value' => $filepath, 'rm' => false], \Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            }

            if ($item->getIsAtOnce()) {
                return $resultRedirect->setPath('*/*/atonce');
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

