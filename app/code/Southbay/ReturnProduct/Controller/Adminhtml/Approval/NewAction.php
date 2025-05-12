<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Approval;

use Magento\Framework\View\Result\PageFactory;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    private $resultPageFactory;

    private $repository;
    private $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context               $context,
        \Southbay\ReturnProduct\Helper\Data               $helper,
        SouthbayReturnProductRepository                   $repository,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory                                       $resultPageFactory
    )
    {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if (!empty($id)) {
            $countries = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);

            if (empty($countries)) {
                $this->messageManager->addErrorMessage(__('Su perfil no esta configurado para autorizar devoluciones'));
                return $this->_redirect('*/*/');
            }

            $item = $this->repository->findById($id);
            if (!is_null($item)) {
                if (!in_array($item->getCountryCode(), $countries)) {
                    $this->messageManager->addErrorMessage(__('Su perfil no esta configurado para autorizar la devolución #') . $item->getId());
                    return $this->_redirect('*/*/');
                }

                $exchange = $this->helper->getLastExchange($item->getCountryCode());

                if (is_null($exchange)) {
                    $this->messageManager->addErrorMessage(__('No tiene cargado una cotización del dolar'));
                    return $this->_redirect('*/*/');
                }

                if ($this->repository->availableForApproval($item)) {
                    $approval_amount = $this->repository->getApprovalAmount($item, $exchange->getExchange());
                    $max_approval_amount = $this->helper->getMaxApprovalAmount($item->getCountryCode())['max_amount'];
                    if (!is_null($max_approval_amount) && $approval_amount > $max_approval_amount) {
                        $this->messageManager->addErrorMessage(__('No está autorizado para aprobar la devolución #') . $item->getId());
                        return $this->_redirect('*/*/');
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('La devolución que intenta aprobar no esta disponible'));
                    return $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addErrorMessage(__('La devolución que intenta aprobar no existe'));
                return $this->_redirect('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Nueva aprobación'));
        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
