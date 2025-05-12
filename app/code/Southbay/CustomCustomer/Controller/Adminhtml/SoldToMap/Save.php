<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\SoldToMap;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\SoldToMapRepository;

class Save extends Action
{
    private $log;
    private $repository;

    public function __construct(
        Action\Context      $context,
        SoldToMapRepository $repository,
        LoggerInterface     $log
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

        try {
            if (isset($params['fields'])) {
                $data = $params['fields'];

                $result = $this->repository->create($data);

                if ($result) {
                    $this->messageManager->addSuccessMessage(__('Datos guardados'));
                } else {
                    $this->messageManager->addSuccessMessage(__('Ya existe el mapeo'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('No data to save.'));
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the data.'));
            $this->log->error($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
