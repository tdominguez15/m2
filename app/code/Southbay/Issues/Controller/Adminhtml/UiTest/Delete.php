<?php

namespace Southbay\Issues\Controller\Adminhtml\UiTest;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Southbay\Issues\Model\ResourceModel\SouthbayUiTestRepository;

class Delete extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $log;

    private $repository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                  $context,
        \Psr\Log\LoggerInterface $log,
        PageFactory              $resultPageFactory,
        SouthbayUiTestRepository $repository
    )
    {
        parent::__construct($context);
        $this->log = $log;
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $id = $params['id'] ?? '';

        $model = null;

        if (!empty($id)) {
            $model = $this->repository->findById($id);
        }

        if (empty($id)) {
            $this->messageManager->addErrorMessage(__("No se indico que registro intenta eliminar"));
        } else if (is_null($model)) {
            $this->messageManager->addErrorMessage(__("No se encontro el registro que intenta eliminar"));
        } else {
            $this->repository->delete($model);
            $this->messageManager->addSuccessMessage(__("Script eliminado"));
        }

        return $resultRedirect->setPath('*/*/');
    }

    private function saveTempFile($filaname, $content)
    {
        // Obtener el directorio "var/tmp/"
        $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $tempDir = 'tmp/';

        if (!$varDirectory->isExist($tempDir)) {
            $varDirectory->create($tempDir);
        }

        $filaname = $tempDir . $filaname;

        $varDirectory->writeFile($filaname, $content);
        return $varDirectory->getAbsolutePath($filaname);
    }
}
