<?php

namespace Southbay\Product\Controller\Adminhtml\Product;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;

class Delete extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                                              $context,
        PageFactory                                                                          $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory                   $repository
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
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
             * @var \Southbay\Product\Model\SouthbayProductImportHistory $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            if ($item) {
                if ($item->getStatus() == SouthbayProductImportHistoryInterface::STATUS_ERROR ||
                    $item->getStatus() == SouthbayProductImportHistoryInterface::STATUS_INIT) {
                    $this->repository->delete($item);
                    $this->messageManager->addSuccessMessage(__('Registro eliminado'));
                } else {
                    $this->messageManager->addErrorMessage(__('El registro está en un estado el cual no permite su eliminación'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('No existe el registro que intenta eliminar'));
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

