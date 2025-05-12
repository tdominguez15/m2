<?php

namespace Southbay\Product\Controller\Adminhtml\ProductExclusion;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Model\ProductExclusionFactory;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ProductExclusionFactory
     */
    protected $productExclusionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProductExclusionFactory $productExclusionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductExclusionFactory $productExclusionFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productExclusionFactory = $productExclusionFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $id = $params['id'] ?? null;

        if (!$id) {
            $this->messageManager->addErrorMessage(__('No se puede buscar el registro que intenta eliminar.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $productExclusion = $this->productExclusionFactory->create()->load($id);
            if (!$productExclusion->getId()) {
                $this->messageManager->addErrorMessage(__('No se puede encontrar el registro que intenta eliminar.'));
                return $resultRedirect->setPath('*/*/');
            }

            $productExclusion->delete();
            $this->messageManager->addSuccessMessage(__('El registro ha sido eliminado exitosamente.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Error occurred while deleting the record: %1', $e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unexpected error occurred: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}
