<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ConfigNotificationRol;

use Magento\Backend\Model\Auth\Session;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;
    private $authSession;

    public function __construct(
        \Magento\Backend\App\Action\Context                                                      $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtvByRolRepository $repository,
        Session                                                                                  $authSession,
        \Psr\Log\LoggerInterface                                                                 $log
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

        $country_code = $params['fields']['southbay_config_notification_rtv_by_rol_country_code'];
        $type = $params['fields']['southbay_config_notification_rtv_by_rol_return_type'];
        $type_rol = $params['fields']['southbay_config_notification_rtv_by_rol_type_rol'];
        $template = $params['fields']['southbay_config_notification_rtv_by_rol_template_code'];
        $status = $params['fields']['southbay_config_notification_rtv_by_rol_status'];

        $model = $this->repository->findOrNew([
            'country' => $country_code,
            'type' => $type,
            'type_rol' => $type_rol,
            'status' => $status
        ]);

        $model->setCountryCode($country_code);
        $model->setType($type);
        $model->setTypeRol($type_rol);
        $model->setTemplateCode($template);
        $model->setStatus($status);

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
