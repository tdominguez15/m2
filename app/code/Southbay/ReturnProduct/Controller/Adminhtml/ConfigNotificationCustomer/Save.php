<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ConfigNotificationCustomer;

use Magento\Backend\Model\Auth\Session;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;
    private $authSession;

    public function __construct(
        \Magento\Backend\App\Action\Context                                                 $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtvRepository $repository,
        Session                                                                             $authSession,
        \Psr\Log\LoggerInterface                                                            $log
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
        $this->log->debug('Saving config notification customer:', ['params' => $params]);

        $country_code = $params['fields']['southbay_config_notification_rtv_country_code'];
        $type = $params['fields']['southbay_config_notification_rtv_return_type'];
        $template = $params['fields']['southbay_config_notification_rtv_template_code'];
        $status = $params['fields']['southbay_config_notification_rtv_status'];

        $model = $this->repository->findOrNew([
            'country' => $country_code,
            'type' => $type,
            'template' => $template,
            'status' => $status
        ]);

        $model->setCountryCode($country_code);
        $model->setType($type);
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
