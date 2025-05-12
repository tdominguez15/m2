<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Approval;

use Magento\Framework\App\ResourceConnection;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;
use Southbay\ReturnProduct\Model\SouthbayReturnFinancialApprovalFactory;
use Magento\Backend\Model\Auth\Session;

class Save extends \Magento\Backend\App\Action
{
    private $log;

    private $return_product_repository;
    private $authSession;

    private $resourceConnection;
    private $southbay_helper;

    public function __construct(
        \Magento\Backend\App\Action\Context                                                                     $context,
        ResourceConnection                                                                                      $resourceConnection,
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalCollectionFactory $collectionFactory,
        SouthbayReturnProductRepository                                                                         $return_product_repository,
        Session                                                                                                 $authSession,
        \Southbay\ReturnProduct\Helper\Data                                                                     $southbay_helper,
        \Psr\Log\LoggerInterface                                                                                $log
    )
    {
        $this->log = $log;
        $this->return_product_repository = $return_product_repository;
        $this->authSession = $authSession;
        $this->resourceConnection = $resourceConnection;
        $this->southbay_helper = $southbay_helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->log->debug('Saving approval:', ['params' => $params]);

        $countries = $this->southbay_helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);

        if (empty($countries)) {
            $this->messageManager->addErrorMessage(__('Su perfil no esta configurado para autorizar devoluciones'));
            return $this->_redirect('*/*/');
        }

        $southbay_return_product_id = $params['fields']['form_fields']['southbay_return_product_id'];
        $approved = $params['fields']['form_fields']['southbay_return_financial_approval_approved'];

        $resultRedirect = $this->resultRedirectFactory->create();
        $return_product = $this->return_product_repository->findById($southbay_return_product_id);

        if (is_null($return_product)) {
            $this->messageManager->addError(__('No existe la devolución #') . $southbay_return_product_id);
            return $resultRedirect->setPath('*/*/');
        } else {
            if (!in_array($return_product->getCountryCode(), $countries)) {
                $this->messageManager->addErrorMessage(__('Su perfil no esta configurado para autorizar la devolución #') . $return_product->getId());
                return $this->_redirect('*/*/');
            }

            $exchange = $this->southbay_helper->getLastExchange($return_product->getCountryCode());

            if (is_null($exchange)) {
                $this->messageManager->addError(__('Falta cargar tipo de cambio'));
                return $resultRedirect->setPath('*/*/');
            }

            $rol_config = $this->southbay_helper->getMaxApprovalAmount($return_product->getCountryCode());
            $max_approval_amount = $rol_config['max_amount'];
            $multiple_approvals = $rol_config['multiple_approvals'];
            $approval_amount = $this->return_product_repository->getApprovalAmount($return_product, $exchange->getExchange());

            if (!is_null($max_approval_amount) && $max_approval_amount < $approval_amount) {
                $this->messageManager->addError(__('No esta autorizado para aprobar la devolución #') . $southbay_return_product_id);
                return $resultRedirect->setPath('*/*/');
            }

            $connection = $this->resourceConnection->getConnection();
            $connection->beginTransaction();
            try {
                $ok = false;

                if ($approved === 'approval') {
                    $ok = true;
                }

                $this->return_product_repository->approval(
                    $return_product,
                    $ok,
                    $this->authSession->getUser()->getId(),
                    $this->authSession->getUser()->getUserName(),
                    $exchange->getExchange(),
                    $multiple_approvals
                );

                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollBack();
                throw $e;
            }

            $this->messageManager->addSuccess(__('Devolución aprobada'));
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
