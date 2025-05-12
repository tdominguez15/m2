<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\RtvRol;

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
        $this->log->debug('Saving rol config:', ['params' => $params]);

        if (isset($params['fields']['southbay_exchange_return_id'])) {
            $id = $params['fields']['southbay_exchange_return_id'];
        } else {
            $id = null;
        }

        $country_code = $params['fields']['southbay_exchange_return_country_code'];
        $exchange = floatval($params['fields']['southbay_exchange_return_exchange']);

        if (is_null($id)) {
            $this->repository->createNewExchange([
                'country' => $country_code,
                'exchange' => $exchange,
                'user_code' => $this->authSession->getUser()->getId(),
                'user_name' => $this->authSession->getUser()->getUserName()
            ]);
        } else {
            $model = $this->repository->findById($id);

            if (is_null($model)) {
                $this->messageManager->addErrorMessage(__('No existe el registro que intenta eliminar'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setExchange($exchange);
            $model->setUserCode($this->authSession->getUser()->getId());
            $model->setUserName($this->authSession->getUser()->getUserName());

            $this->repository->save($model);
        }

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
