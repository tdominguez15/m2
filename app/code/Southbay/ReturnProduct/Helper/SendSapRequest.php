<?php

namespace Southbay\ReturnProduct\Helper;

use Magento\Framework\App\Helper\Context;
use Southbay\CustomCustomer\Helper\SouthbayCustomerHelper;
use Southbay\CustomCustomer\Model\MapCountryRepository;

class SendSapRequest extends SendSapRtvRequest
{
    const DEFAULT_SAP_DOC = 'Z001';
    const DEFAULT_SAP_CHANNEL = '20';

    public function __construct(Context $context, \Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory $shipToCollectionFactory, \Southbay\CustomCustomer\Model\ResourceModel\ShipToMap\CollectionFactory $shipToMapCollectionFactory, \Southbay\ReturnProduct\Model\SouthbaySapDocFactory $sapDocFactory, \Southbay\ReturnProduct\Model\SouthbaySapDocItemFactory $sapDocItemFactory, \Southbay\ReturnProduct\Model\SouthbaySapInterfaceFactory $sapInterfaceFactory, \Southbay\ReturnProduct\Model\SouthbaySapCheckStatusFactory $sapCheckStatusFactory, \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDoc $sapDocRepository, \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocItem $sapDocItemRepository, \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterface $sapInterfaceRepository, \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapCheckStatus $sapCheckStatusRepository, \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapDocCollectionFactory $sapDocCollectionFactory, \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapDocItemCollectionFactory $sapDocItemCollectionFactory, \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapInterfaceCollectionFactory $sapInterfaceCollectionFactory, \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapCheckStatusCollectionFactory $southbaySapCheckStatusCollectionFactory, \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $returnProductRepository, \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository $returnProductItemRepository, \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceRepository $invoiceRepository, \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository $invoiceItemRepository, \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterfaceConfigRepository $sapInterfaceConfigRepository, SouthbayCustomerHelper $customerHelper, \Psr\Log\LoggerInterface $log, MapCountryRepository $mapCountryRepository)
    {
        parent::__construct($context, $shipToCollectionFactory, $shipToMapCollectionFactory, $sapDocFactory, $sapDocItemFactory, $sapInterfaceFactory, $sapCheckStatusFactory, $sapDocRepository, $sapDocItemRepository, $sapInterfaceRepository, $sapCheckStatusRepository, $sapDocCollectionFactory, $sapDocItemCollectionFactory, $sapInterfaceCollectionFactory, $southbaySapCheckStatusCollectionFactory, $returnProductRepository, $returnProductItemRepository, $invoiceRepository, $invoiceItemRepository, $sapInterfaceConfigRepository, $customerHelper, $log, $mapCountryRepository);
    }

    public function sapOrderRequest($data, $type = '')
    {
        $config = $this->sapInterfaceConfigRepository->getConfigByType(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::TYPE_PURCHASE_ORDER);

        if (is_null($config)) {
            return false;
        }

        $map = $this->mapCountryRepository->toMap();

        $request = [
            'row' => [
                // "AUART" => $data['doc'] ?? self::DEFAULT_SAP_DOC,
                "AUART" => $data['doc'],
                "VKORG" => $map[$data['country_code']],
                "VTWEG" => $data['channel'] ?? self::DEFAULT_SAP_CHANNEL,
                "SPART" => '01',
                "BSTKD" => 'B2B-' . substr($data['observation'], 0, 31),
                "KUNNR_AG" => $data['sold_to'],
                "KUNNR_WE" => $data['ship_to'],
                "REQ_DATE_H" => $data['season_date'],
                "VSBED" => '10',
                "TEXTO" => 'B2B-#' . $data['customer_order_id'],
                "ITEMS" => []
            ]
        ];

        foreach ($data['items'] as $item) {
            $request['row']['ITEMS'][] =
                [
                    "MATNR" => strval($item['variant']),
                    "KWMENG" => strval($item['qty'])
                ];
        }

        if ($this->checkFutureRequestExists($data['id'], $request)) {
            return true;
        }

        $model = $this->createNewSapRequest($config->getUrl(), $data['id'], 'futures');

        $request['row']["DATE"] = date('d.m.Y');
        $request['row']["IHREZ"] = $model->getId();

        $this->updateRequest($model, $request);

        return $this->sendSapRequest($config->getUrl(), [['model' => $model]]);
    }
}
