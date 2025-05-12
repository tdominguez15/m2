<?php

namespace Southbay\Product\Controller\Adminhtml\Season;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Delete extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;

    private $orderCollectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                        $context,
        PageFactory                                                    $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\Season\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\Season                   $repository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory     $orderCollectionFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
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
             * @var \Southbay\Product\Model\Season $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            if ($item) {
                $collection = $this->orderCollectionFactory->create();
                $collection->addFieldToFilter('ext_order_id', $id);
                if ($collection->getSize() == 0) {
                    $this->repository->delete($item);
                    $this->messageManager->addSuccessMessage(__('Registro eliminado'));
                } else {
                    $this->messageManager->addErrorMessage(__('Este registro ya está asociado una o más ordenes'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('No existe el registro que intenta eliminar'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}
