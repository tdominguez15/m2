<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ConfigByRol;

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
        $type_rol = $params['fields']['southbay_rol_config_return_type_rol'];
        $rol_code = $params['fields']['southbay_rol_config_return_rol_code'];

        $model = $this->repository->findOrNew([
            'country' => $country_code,
            'type' => $type,
            'type_rol' => $type_rol,
            'rol_code' => $rol_code
        ]);

        $model->setCountryCode($country_code);
        $model->setType($type);
        $model->setTypeRol($type_rol);
        $model->setRolCode($rol_code);
        $model->setApprovalUseAmountLimit(null);
        $model->setApprovalAmountLimit(null);

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
