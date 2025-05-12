<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\ShipTo;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\ShipToRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class Save
 * @package Southbay\CustomCustomer\Controller\Adminhtml\ShipTo
 */
class Save extends Action
{
    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var ShipToRepository
     */
    private $repository;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param ShipToRepository $repository
     * @param LoggerInterface $log
     */
    public function __construct(
        Action\Context   $context,
        ShipToRepository $repository,
        LoggerInterface  $log
    )
    {
        $this->repository = $repository;
        $this->log = $log;
        parent::__construct($context);
    }

    /**
     * Execute method to save ShipTo data
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['fields'])) {
                $data = $params['fields'];

                $is_internal = $data['southbay_ship_to_is_internal'] ?? 0;

                $shipToData = [
                    'southbay_ship_to_id' => $data['southbay_ship_to_id'] ?? null,
                    'southbay_ship_to_customer_code' => $data['southbay_ship_to_customer_code'] ?? '',
                    'southbay_ship_to_name' => $data['southbay_ship_to_name'] ?? '',
                    'southbay_ship_to_code' => $data['southbay_ship_to_code'] ?? '',
                    'southbay_ship_to_old_code' => $data['southbay_ship_to_old_code'] ?? '',
                    'southbay_ship_to_address' => $data['southbay_ship_to_address'] ?? '',
                    'southbay_ship_to_address_number' => $data['southbay_ship_to_address_number'] ?? '',
                    'southbay_ship_to_state' => $data['southbay_ship_to_state'] ?? '',
                    'southbay_ship_to_country_code' => $data['southbay_ship_to_country_code'] ?? '',
                    'southbay_ship_to_is_internal' => $is_internal
                ];

                $this->repository->createOrUpdate($shipToData);

                $this->messageManager->addSuccessMessage(__('Datos guardados'));
            } else {
                $this->messageManager->addErrorMessage(__('No data to save.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->log->error($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the data.'));
            $this->log->error($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
