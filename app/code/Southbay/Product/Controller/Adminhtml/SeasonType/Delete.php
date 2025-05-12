<?php

namespace Southbay\Product\Controller\Adminhtml\SeasonType;

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

    private $seasonCollectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                            $context,
        PageFactory                                                        $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SeasonType\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\Season\CollectionFactory     $seasonCollectionFactory,
        \Southbay\Product\Model\ResourceModel\SeasonType                   $repository
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->seasonCollectionFactory = $seasonCollectionFactory;
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
             * @var \Southbay\Product\Model\SeasonType $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            if ($item) {
                $collection = $this->seasonCollectionFactory->create();
                $collection->addFieldToFilter('season_type_code', $item->getSeasonTypeCode());
                if ($collection->getSize() == 0) {
                    $this->repository->delete($item);
                    $this->messageManager->addSuccessMessage(__('Registro eliminado'));
                } else {
                    $this->messageManager->addErrorMessage(__('Este registro ya está asociado una o más temporadas'));
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
