<?php

namespace Southbay\Product\Controller\Adminhtml\Season;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\Product\Helper\ProductData;

class DownloadProductList extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;

    private $fileFactory;

    private $filesystem;

    private $storeManager;

    private $productData;

    private $directoryList;

    private $ioFile;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                            $context,
        PageFactory                                                        $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\Season\CollectionFactory     $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory $repository,
        StoreManagerInterface                                              $storeManager,
        FileFactory                                                        $fileFactory,
        Filesystem                                                         $filesystem,
        DirectoryList                                                      $directoryList,
        IoFile                                                             $ioFile,
        ProductData                                                        $productData
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->productData = $productData;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
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
             * @var \Southbay\Product\Model\Season $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            if ($item) {
                $base_path = 'export';

                $directoryPath = $this->directoryList->getPath('var') . '/' . $base_path;

                if (!$this->ioFile->fileExists($directoryPath, false)) {
                    $this->ioFile->mkdir($directoryPath, 0775);
                }

                $filename = 'product-list-' . date('Y-m-d') . '.csv';
                $filepath = $directoryPath . '/' . $filename;

                $this->productData->listProductByStoreId($item->getStoreId(), $item->getSeasonCategoryId(), true, $filepath);

                return $this->fileFactory->create($filename, ['type' => 'filename', 'value' => $filepath, 'rm' => true], 'var');
            } else {
                $this->messageManager->addErrorMessage(__('No existe la temporada'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}
