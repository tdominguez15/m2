<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\SoldToMap;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Southbay\CustomCustomer\Model\SoldToMapRepository;

class Delete extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context             $context,
        PageFactory         $resultPageFactory,
        SoldToMapRepository $repository
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $id = $params['id'];

        if (!$id) {
            $this->messageManager->addErrorMessage(__('No se puede buscar el registro que intenta eliminar'));
        } else {
            if (!empty($id)) {
                $this->repository->deleteById([$id]);
                $this->messageManager->addSuccessMessage(__('Registro eliminado'));
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
