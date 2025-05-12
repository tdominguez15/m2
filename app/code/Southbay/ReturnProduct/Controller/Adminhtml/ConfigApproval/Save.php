<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ConfigApproval;

use Magento\Backend\Model\Auth\Session;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;
    private $authSession;

    public function __construct(
        \Magento\Backend\App\Action\Context                                        $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayRolConfigRtvRepository $repository,
        Session                                                                    $authSession,
        \Psr\Log\LoggerInterface                                                   $log
    )
    {
        $this->log = $log;
        $this->repository = $repository;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $this->log->debug('Saving config approval:', ['params' => $params]);

        $country_code = $params['fields']['southbay_rol_config_return_country_code'];
        $type = $params['fields']['southbay_rol_config_return_type'];
        $rol_code = $params['fields']['southbay_rol_config_return_rol_code'];
        $use_max_amount = $params['fields']['southbay_rol_config_return_approval_use_amount_limit'] == 'true';
        $require_all_members = $params['fields']['require_all_members'] == 'true';
        $max_amount = floatval($params['fields']['southbay_rol_config_return_approval_amount_limit']);

        $model = $this->repository->findOrNew([
            'country' => $country_code,
            'type' => $type,
            'type_rol' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL,
            'rol_code' => $rol_code
        ]);

        $model->setCountryCode($country_code);
        $model->setType($type);
        $model->setTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);
        $model->setRolCode($rol_code);
        $model->setApprovalUseAmountLimit($use_max_amount);
        $model->setApprovalAmountLimit($max_amount);
        $model->setRequireAllMembers($require_all_members);

        $this->repository->save($model);

        $this->messageManager->addSuccessMessage(__('Datos guardados'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
