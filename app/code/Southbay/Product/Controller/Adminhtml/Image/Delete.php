<?php

namespace Southbay\Product\Controller\Adminhtml\Image;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Cron\SouthbayProductImportImgCron;

class Delete extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;
    private $filesystem;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                                                 $context,
        PageFactory                                                                             $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory                   $repository,
        Filesystem                                                                              $filesystem
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $id = $params['id'];

        if (!$id) {
            $this->messageManager->addErrorMessage(__('No se puede buscar el registro que intenta eliminar'));
        } else {
            /**
             * @var \Southbay\Product\Model\SouthbayProductImportImgHistory $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            if ($item) {
                $this->deteleFolder($item->getName());
                $this->repository->delete($item);
                $this->messageManager->addSuccessMessage(__('Registro eliminado'));
            } else {
                $this->messageManager->addErrorMessage(__('No existe el registro que intenta eliminar'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    private function deteleFolder($name)
    {
        $media_folder = SouthbayProductImportImgCron::UPLOAD_PATH . '/' . $name;
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $target = $mediaDirectory->getAbsolutePath($media_folder);

        if (file_exists($target)) {
            shell_exec('rm -Rf ' . $target);
        }
    }

    public function _isAllowed()
    {
        return true;
    }
}
