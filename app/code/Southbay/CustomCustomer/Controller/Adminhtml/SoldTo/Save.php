<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\SoldTo;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\CustomCustomer\Model\SoldToRepository;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    private $log;
    private $repository;

    private $mapCountryRepository;

    public function __construct(
        Action\Context       $context,
        SoldToRepository     $repository,
        MapCountryRepository $mapCountryRepository,
        LoggerInterface      $log
    )
    {
        $this->log = $log;
        $this->repository = $repository;
        $this->mapCountryRepository = $mapCountryRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();


        try {
            if (isset($params['fields'])) {
                $data = $params['fields'];

                $countryCode = $data['southbay_sold_to_country_code'] ?? null;
                if (!empty($countryCode)) {
                    $map = $this->mapCountryRepository->findByCountryCode($countryCode);
                    if (is_null($map)) {
                        $sapCountryCode = '';
                    } else {
                        $sapCountryCode = $map->getSapCountryCode();
                    }
                    /*
                    $sapCountryCode = match ($countryCode) {
                        'UY' => 'B01P',
                        'AR' => 'A01P',
                        default => ''
                    };
                    */
                }
                $channelCode = 1;
                $sectionCode = 1;
                $customerCode = $data['southbay_sold_to_customer_code'] ?? "";
                $customerCodeOld = $data['southbay_sold_to_customer_code_old'] ?? "";
                $countryBusinessCode = !empty($data['southbay_sold_to_country_business_code']) ? $data['southbay_sold_to_country_business_code'] : '000000000';                $customerName = $data['southbay_sold_to_customer_name'] ?? "";
                $segmentation = $data['southbay_sold_to_segmentation'] ?? '';
                if (is_array($segmentation)) {
                    $segmentation = implode(' ', $segmentation);
                }
                $locked = $data['southbay_sold_to_locked'] ?? 0;
                $is_internal = $data['southbay_sold_to_is_internal'] ?? 0;
                $automaticallyAuthorizePurchases = $data['southbay_sold_to_automatically_authorize_purchases'] ?? 0;

                $soldToData = [
                    'southbay_sold_to_country_code' => $countryCode,
                    'southbay_sold_to_sap_country_code' => $sapCountryCode,
                    'southbay_sold_to_channel_code' => $channelCode,
                    'southbay_sold_to_section_code' => $sectionCode,
                    'southbay_sold_to_customer_code' => $customerCode,
                    'southbay_sold_to_customer_code_old' => $customerCodeOld,
                    'southbay_sold_to_country_business_code' => $countryBusinessCode,
                    'southbay_sold_to_customer_name' => $customerName,
                    'southbay_sold_to_segmentation' => $segmentation,
                    'southbay_sold_to_locked' => $locked,
                    'southbay_sold_to_is_internal' => $is_internal,
                    'southbay_sold_to_automatically_authorize_purchases' => $automaticallyAuthorizePurchases
                ];

                $this->repository->createOrUpdate($soldToData);

                $this->messageManager->addSuccessMessage(__('Datos guardados'));
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
