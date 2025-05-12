<?php

namespace Southbay\Product\Controller\Adminhtml\Image;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Api\Data\SouthbayProductImportImgHistoryInterface;

class Retry extends Action
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
        Context                                                                                 $context,
        PageFactory                                                                             $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory                   $repository
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
            $this->messageManager->addErrorMessage(__('No se puede buscar el registro que intenta modificar'));
        } else {
            /**
             * @var \Southbay\Product\Model\SouthbayProductImportImgHistory $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            if ($item) {
                if ($item->getStatus() != SouthbayProductImportImgHistoryInterface::STATUS_INIT) {
                    $item->setStatus(SouthbayProductImportImgHistoryInterface::STATUS_INIT);
                    $item->setStartImportDate(null);
                    $item->setendImportDate(null);
                    $item->setResultMsg('');
                    $item->setTotalFiles(0);
                    $this->repository->save($item);
                    $this->messageManager->addSuccessMessage(__('Registro actualizado'));
                } else {
                    $this->messageManager->addErrorMessage(__('El paquete de imagenes está en un estado el cual no permite reintentar'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('No existe el registro que intenta modificar'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

