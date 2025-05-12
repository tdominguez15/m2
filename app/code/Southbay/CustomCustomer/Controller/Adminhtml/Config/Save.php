<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\Config;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;
    private $soldToRepositoryrepository;

    public function __construct(
        \Magento\Backend\App\Action\Context                     $context,
        \Southbay\CustomCustomer\Model\CustomerConfigRepository $repository,
        \Southbay\CustomCustomer\Model\SoldToRepository         $soldToRepositoryrepository,
        \Psr\Log\LoggerInterface                                $log
    )
    {
        $this->log = $log;
        $this->repository = $repository;
        $this->soldToRepositoryrepository = $soldToRepositoryrepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $this->log->debug('Saving customer config:', ['params' => $params]);

        $email = $params['fields']['magento_customer_email'];
        $sold_to_ids = $params['fields']['southbay_customer_config_sold_to_ids'];
        $functions_codes = $params['fields']['southbay_customer_config_functions_codes'];
        $countries_codes = null;

        if (!empty($sold_to_ids)) {
            $countries_codes = [];
            foreach ($sold_to_ids as $id) {
                $field = $this->soldToRepositoryrepository->getById($id);
                if (!in_array($field->getCountryCode(), $countries_codes)) {
                    $countries_codes[] = $field->getCountryCode();
                }
            }
        } else {
            $sold_to_ids = null;
        }

        $this->repository->save(
            [
                'email' => $email,
                'sold_to_ids' => (is_null($sold_to_ids) ? $sold_to_ids : implode(',', $sold_to_ids)),
                'countries_codes' => (is_null($countries_codes) ? $countries_codes : implode(',', $countries_codes)),
                'functions_codes' => (empty($functions_codes) ? null : implode(',', $functions_codes))
            ]
        );

        $this->messageManager->addSuccessMessage(__('Datos guardados'));
        return $resultRedirect->setPath('*/*/');
    }
}
