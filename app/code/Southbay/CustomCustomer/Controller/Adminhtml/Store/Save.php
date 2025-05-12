<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\Store;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;

    public function __construct(
        \Magento\Backend\App\Action\Context                  $context,
        \Southbay\CustomCustomer\Model\ConfigStoreRepository $repository,
        \Psr\Log\LoggerInterface                             $log
    )
    {
        $this->log = $log;
        $this->repository = $repository;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $this->log->debug('Saving store config:', ['params' => $params]);

        $country_code = $params['fields']['southbay_country_code'];
        $store_code = $params['fields']['southbay_store_code'];
        $function_code = $params['fields']['southbay_function_code'];

        $this->repository->createOrUpdate(
            [
                'country_code' => $country_code,
                'store_code' => $store_code,
                'function_code' => $function_code
            ]
        );

        $this->messageManager->addSuccessMessage(__('Datos guardados'));
        return $resultRedirect->setPath('*/*/');
    }
}
