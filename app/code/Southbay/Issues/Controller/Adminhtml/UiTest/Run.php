<?php

namespace Southbay\Issues\Controller\Adminhtml\UiTest;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Southbay\Issues\Model\ResourceModel\SouthbayUiTestRepository;

class Run extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $log;

    private $filesystem;

    private $repository;

    private $session;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                    $context,
        \Psr\Log\LoggerInterface                                   $log,
        Filesystem                                                 $filesystem,
        PageFactory                                                $resultPageFactory,
        BookmarkInterfaceFactory                                   $bookmarkFactory,
        \Magento\Ui\Model\ResourceModel\Bookmark\CollectionFactory $bookmarkCollectionFactory,
        SouthbayUiTestRepository                                   $repository
    )
    {
        parent::__construct($context);
        $this->log = $log;
        $this->filesystem = $filesystem;
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->session = $context->getSession();
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();

        $this->log->info('Init script execution...', ['params' => $params]);

        $filaname = 'script_execution_' . time() . '.php';

        $id = $params['fields']['entity_id'] ?? '';
        $name = $params['fields']['name'];
        $description = $params['fields']['description'];

        $content = $params['fields']['content'];
        $result = [];

        if (!empty($id)) {
            $model = $this->repository->findById($id);
        } else {
            $model = $this->repository->getNewModel();
        }

        $model->setName($name);
        $model->setDescription($description);
        $model->setContent($content);
        $model->setResult(json_encode($result));

        $this->repository->save($model);

        if (empty(!$content)) {
            $this->session->setRunModelId($model->getId());
            $temp_file = $this->saveTempFile($filaname, $content);

            $_out = [];

            $out = function ($message, $context = []) use (&$_out) {
                $_out[] = ['message' => $message, 'context' => $context];
            };

            (function () use ($temp_file, $out) {
                require_once $temp_file;
            })();

            $model->setResult(json_encode($_out));
            $model->setTotalExecution($model->getTotalExecution() + 1);
            $this->repository->save($model);

            $this->session->unsRunModelId();

            $this->messageManager->addSuccessMessage(__("Script ejecutado"));
        } else {
            $this->messageManager->addWarningMessage(__("Datos vacios"));
        }

        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
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
