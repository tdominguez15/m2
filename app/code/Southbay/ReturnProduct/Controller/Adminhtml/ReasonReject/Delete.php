<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ReasonReject;

class Delete extends \Magento\Backend\App\Action
{
    private $repository;
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context                                                          $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReasonReject                             $repository,
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReasonRejectCollectionFactory $collectionFactory
    )
    {
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->_request->getParam('id');

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();

        $item = $collection->getItemById($id);

        if (is_null($item)) {
            $this->getMessageManager()->addErrorMessage(__('No existe el motivo que intenta eliminar'));
            return $this->_redirect('*/*/');
        }

        $this->repository->delete($item);

        $this->getMessageManager()->addSuccessMessage(__('Motivo eliminado exitosamente'));
        return $this->_redirect('*/*/');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
