<?php

namespace Southbay\ReturnProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Southbay\CustomCustomer\Helper\SouthbayCustomerHelper;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\ReturnProduct\Api\Data\SouthbaySapDoc;
use Southbay\ReturnProduct\Api\Data\SouthbaySapInterface;

class SendSapRtvRequest extends AbstractHelper
{
    private $sapDocFactory;
    private $sapDocItemFactory;
    private $sapInterfaceFactory;
    private $sapDocRepository;
    private $sapDocItemRepository;
    private $sapInterfaceRepository;
    private $sapDocCollectionFactory;
    private $sapDocItemCollectionFactory;
    private $sapInterfaceCollectionFactory;

    private $returnProductRepository;
    private $returnProductItemRepository;
    private $invoiceRepository;
    private $log;
    private $customerHelper;
    private $invoiceItemRepository;

    private $southbaySapCheckStatusCollectionFactory;
    private $sapCheckStatusRepository;
    private $sapCheckStatusFactory;

    private $shipToCollectionFactory;

    protected $sapInterfaceConfigRepository;

    protected $mapCountryRepository;

    private $shipToMapCollectionFactory;

    public function __construct(Context                                                                                        $context,
                                \Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory                          $shipToCollectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\ShipToMap\CollectionFactory                       $shipToMapCollectionFactory,
                                \Southbay\ReturnProduct\Model\SouthbaySapDocFactory                                            $sapDocFactory,
                                \Southbay\ReturnProduct\Model\SouthbaySapDocItemFactory                                        $sapDocItemFactory,
                                \Southbay\ReturnProduct\Model\SouthbaySapInterfaceFactory                                      $sapInterfaceFactory,
                                \Southbay\ReturnProduct\Model\SouthbaySapCheckStatusFactory                                    $sapCheckStatusFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDoc                                     $sapDocRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocItem                                 $sapDocItemRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterface                               $sapInterfaceRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapCheckStatus                             $sapCheckStatusRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapDocCollectionFactory         $sapDocCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapDocItemCollectionFactory     $sapDocItemCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapInterfaceCollectionFactory   $sapInterfaceCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapCheckStatusCollectionFactory $southbaySapCheckStatusCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository                    $returnProductRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository                $returnProductItemRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceRepository                          $invoiceRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository                      $invoiceItemRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterfaceConfigRepository               $sapInterfaceConfigRepository,
                                SouthbayCustomerHelper                                                                         $customerHelper,
                                \Psr\Log\LoggerInterface                                                                       $log,
                                MapCountryRepository                                                                           $mapCountryRepository
    )
    {
        $this->log = $log;
        $this->customerHelper = $customerHelper;

        $this->sapDocFactory = $sapDocFactory;
        $this->sapDocItemFactory = $sapDocItemFactory;
        $this->sapInterfaceFactory = $sapInterfaceFactory;

        $this->sapDocRepository = $sapDocRepository;
        $this->sapDocItemRepository = $sapDocItemRepository;
        $this->sapInterfaceRepository = $sapInterfaceRepository;

        $this->sapDocCollectionFactory = $sapDocCollectionFactory;
        $this->sapDocItemCollectionFactory = $sapDocItemCollectionFactory;
        $this->sapInterfaceCollectionFactory = $sapInterfaceCollectionFactory;

        $this->returnProductRepository = $returnProductRepository;
        $this->returnProductItemRepository = $returnProductItemRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceItemRepository = $invoiceItemRepository;

        $this->sapCheckStatusFactory = $sapCheckStatusFactory;
        $this->sapCheckStatusRepository = $sapCheckStatusRepository;
        $this->southbaySapCheckStatusCollectionFactory = $southbaySapCheckStatusCollectionFactory;

        $this->shipToCollectionFactory = $shipToCollectionFactory;

        $this->sapInterfaceConfigRepository = $sapInterfaceConfigRepository;

        $this->mapCountryRepository = $mapCountryRepository;
        $this->shipToMapCollectionFactory = $shipToMapCollectionFactory;

        parent::__construct($context);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return bool
     */
    public function send($id)
    {
        $this->log->debug('Sending rtv', ['id' => $id]);

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
         */
        $model = $this->returnProductRepository->findById($id);
        if (is_null($model)) {
            $this->log->error('Sending rtv: not found', ['id' => $id]);
            return false;
        }

        $items = $this->returnProductItemRepository->findByReturnId($id);

        if (empty($items)) {
            $this->log->error('Sending rtv: without items', ['id' => $id]);
            return false;
        }

        $no_innova_ar_max_date = [];
        $no_innova_uy = [];
        $no_innova_ar = [];
        $innova = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem $item
         */
        foreach ($items as $item) {
            if ($item->getQtyAccepted() <= 0) {
                continue;
            }

            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoice $invoice
             */
            $invoice = $this->invoiceRepository->findById($item->getInvoiceid());

            if (is_null($invoice)) {
                return false;
            }

            $invoice_date = strtotime($invoice->getInvoiceDate());

            if ($invoice->getOldInvoice()) {
                if ($model->getCountryCode() == 'UY') {
                    if (!isset($no_innova_uy[$invoice->getIntInvoiceNum()])) {
                        $no_innova_uy[$invoice->getIntInvoiceNum()] = ['items' => []];

                        $ship_to = $this->findShipTo($model->getCustomerCode(), $invoice->getCustomerShipToCode(), $invoice->getCustomerCode());

                        if (is_null($ship_to)) {
                            $this->log->debug('Ship to not found', [
                                'id' => $id,
                                'invoice_id' => $invoice->getId(),
                                'customer_code' => $model->getCustomerCode(),
                                'invoice_customer_code' => $invoice->getCustomerCode(),
                                'ship_to_code' => $invoice->getCustomerShipToCode()]);
                            return false;
                        }

                        $no_innova_uy[$invoice->getIntInvoiceNum()]['country_code'] = $model->getCountryCode();
                        $no_innova_uy[$invoice->getIntInvoiceNum()]['sold_to_code'] = $ship_to->getCustomerCode();
                        $no_innova_uy[$invoice->getIntInvoiceNum()]['ship_to_code'] = $ship_to->getCode();
                        $no_innova_uy[$invoice->getIntInvoiceNum()]['invoice_ref'] = $invoice->getInvoiceRef();
                        $no_innova_uy[$invoice->getIntInvoiceNum()]['invoice_date'] = date('d.m.Y', $invoice_date);
                    }

                    if (!isset($no_innova_uy[$invoice->getIntInvoiceNum()]['items'][$item->getSku()])) {
                        $invoice_item = $this->invoiceItemRepository->findById($item->getInvoiceItemId());
                        $no_innova_uy[$invoice->getIntInvoiceNum()]['items'][$item->getSku()] =
                            [
                                'qty' => 0,
                                'amount' => $item->getNetUnitPrice(),
                                'bu' => $invoice_item->getBu(),
                                'sku' => $item->getSku(),
                                'name' => $item->getName()
                            ];
                    }
                    $no_innova_uy[$invoice->getIntInvoiceNum()]['items'][$item->getSku()]['qty'] += $item->getQtyAccepted();
                } else if ($model->getCountryCode() == 'AR') {
                    if (!isset($no_innova_ar[$invoice->getCustomerShipToCode()])) {
                        $no_innova_ar[$invoice->getCustomerShipToCode()] = ['items' => [], 'invoices' => []];
                        $no_innova_ar_max_date[$invoice->getCustomerShipToCode()] = $invoice_date;

                        $this->log->debug('search ship to an sold to:', ['id' => $id, 'customer' => $model->getCustomerCode(), 'ship_to' => $invoice->getCustomerShipToCode()]);
                        $ship_to = $this->findShipTo($model->getCustomerCode(), $invoice->getCustomerShipToCode(), $invoice->getCustomerCode());

                        if (is_null($ship_to)) {
                            $this->log->debug('Ship to not found', ['id' => $id, 'invoice_id' => $invoice->getId(),
                                'customer_code' => $model->getCustomerCode(),
                                'invoice_customer_code' => $invoice->getCustomerCode(),
                                'ship_to_code' => $invoice->getCustomerShipToCode()]);
                            return false;
                        }

                        $no_innova_ar[$invoice->getCustomerShipToCode()]['country_code'] = $model->getCountryCode();
                        $no_innova_ar[$invoice->getCustomerShipToCode()]['sold_to_code'] = $ship_to->getCustomerCode();
                        $no_innova_ar[$invoice->getCustomerShipToCode()]['ship_to_code'] = $ship_to->getCode();
                    } else if ($invoice_date > $no_innova_ar_max_date[$invoice->getCustomerShipToCode()]) {
                        $no_innova_ar_max_date[$invoice->getCustomerShipToCode()] = $invoice_date;
                    }

                    if (!in_array($invoice->getInvoiceRef(), $no_innova_ar[$invoice->getCustomerShipToCode()]['invoices'])) {
                        $no_innova_ar[$invoice->getCustomerShipToCode()]['invoices'][] = $invoice->getInvoiceRef();
                    }

                    $no_innova_ar[$invoice->getCustomerShipToCode()]['invoice_ref'] = $invoice->getInvoiceRef();
                    $no_innova_ar[$invoice->getCustomerShipToCode()]['invoice_date'] = date('d.m.Y', $invoice_date);

                    if (!isset($no_innova_ar[$invoice->getCustomerShipToCode()]['items'][$item->getSku()])) {
                        $invoice_item = $this->invoiceItemRepository->findById($item->getInvoiceItemId());
                        $no_innova_ar[$invoice->getCustomerShipToCode()]['items'][$item->getSku()] =
                            [
                                'qty' => 0,
                                'bu' => $invoice_item->getBu(),
                                'sku' => $item->getSku(),
                                'name' => $item->getName(),
                                'amount' => $item->getNetUnitPrice()
                            ];
                    }
                    $no_innova_ar[$invoice->getCustomerShipToCode()]['items'][$item->getSku()]['qty'] += $item->getQtyAccepted();
                }
            } else {
                if (!isset($innova[$invoice->getIntInvoiceNum()])) {
                    $innova[$invoice->getIntInvoiceNum()] = ['items' => []];

                    $ship_to = $this->findShipTo($model->getCustomerCode(), $invoice->getCustomerShipToCode(), $invoice->getCustomerCode());

                    if (is_null($ship_to)) {
                        $this->log->debug('Ship to not found', ['id' => $id, 'invoice_id' => $invoice->getId(),
                            'customer_code' => $model->getCustomerCode(),
                            'invoice_customer_code' => $invoice->getCustomerCode(),
                            'ship_to_code' => $invoice->getCustomerShipToCode()]);
                        return false;
                    }

                    $innova[$invoice->getIntInvoiceNum()]['country_code'] = $model->getCountryCode();
                    $innova[$invoice->getIntInvoiceNum()]['sold_to_code'] = $ship_to->getCustomerCode();
                    $innova[$invoice->getIntInvoiceNum()]['ship_to_code'] = $ship_to->getCode();
                }

                $innova[$invoice->getIntInvoiceNum()]['type'] = $model->getType();
                $innova[$invoice->getIntInvoiceNum()]['invoice_ref'] = $invoice->getInvoiceRef();
                $innova[$invoice->getIntInvoiceNum()]['invoice_date'] = date('d.m.Y', $invoice_date);

                $invoice_item = $this->invoiceItemRepository->findById($item->getInvoiceItemId());

                $pos = $invoice_item->getPosition();
                $item_key = $invoice_item->getSkuVariant() . '-' . ($pos ?? '*');

                if (!isset($innova[$invoice->getIntInvoiceNum()]['items'][$item_key])) {
                    $invoice_item = $this->invoiceItemRepository->findById($item->getInvoiceItemId());
                    $innova[$invoice->getIntInvoiceNum()]['items'][$item_key] =
                        [
                            'qty' => 0,
                            'position' => $pos, // colo car posicion
                            'sku_variant' => $invoice_item->getSkuVariant(),
                            'sku' => $item->getSku()
                        ];
                }
                $innova[$invoice->getIntInvoiceNum()]['items'][$item_key]['qty'] += $item->getQtyAccepted();
                $this->log->debug('Innova item', $innova);
            }
        }

        $this->log->debug('Start sap communication...', ['ar_no_innova' => $no_innova_ar, 'uy_no_innova' => $no_innova_uy, 'innova' => $innova]);

        $result = false;
        $has_no_innova = false;

        if (!empty($no_innova_ar)) {
            $has_no_innova = true;
            $result = $this->noInnovaAr($id, $no_innova_ar, $no_innova_ar_max_date);
        } else if (!empty($no_innova_uy)) {
            $has_no_innova = true;
            $result = $this->noInnovaUy($id, $no_innova_uy);
        }

        if (
            (($result && $has_no_innova) && !empty($innova)) ||
            (!$has_no_innova && !empty($innova))
        ) {
            $result = $this->innova($id, $innova);
        }

        if ($result) {
            $items = $this->getDocs($id, 'rtv');

            if ($items) {
                $total_qty = $this->getTotalQty($items);
            } else {
                $total_qty = false;
            }
            if ($total_qty === false) {
                $this->log->error('Error getting total qty', ['return_product_id' => $model->getId(), 'total_accepted' => $model->getTotalAccepted()]);
                $result = false;
            } else if ($total_qty != $model->getTotalAccepted()) {
                $this->log->error('Total sap documents diff qty from return product', [
                    'return_product_id' => $model->getId(), 'total_qty' => $total_qty, 'total_accepted' => $model->getTotalAccepted()
                ]);
                $result = false;
            }
        }

        $this->log->debug('End sap communication');

        return $result;
    }

    private function innova($id, $innova)
    {
        // $url = \Southbay\ReturnPro
        //duct\Api\Data\SouthbaySapInterface::SAP_ENDPOINT_INNOVA;
        $config = $this->sapInterfaceConfigRepository->getConfigByType(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::TYPE_INNOVA);

        if (is_null($config)) {
            return false;
        }

        $requests = [];
        $map = $this->mapCountryRepository->toMap();

        foreach ($innova as $key => $item) {
            $request = [
                "row" => [
                    "VBELN" => $key,
                    "AUART" => ($item['type'] == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD ? "Z100" : "Z107"),
                    "AUGRU" => ($item['type'] == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD ? "900" : "220"),
                    "VKORG" => $map[$item['country_code']], // $item['country_code'] == 'AR' ? 'A01P' : 'B01P',
                    "SPART" => "01",
                    "VTWEG" => "20",
                    "BSTKD" => "B2B-#" . $id,
                    "TEXTO" => '',
                    "ITEMS" => []
                ]
            ];

            foreach ($item['items'] as $_item) {
                $request['row']['ITEMS'][] = [
                    "MATNR" => $_item['sku_variant'],
                    "KWMENG" => strval($_item['qty']),
                    "POSNR" => strval($_item['position'])
                ];
            }

            if ($this->checkRtvRequestExists($id, $request)) {
                continue;
            }

            $model = $this->createNewSapRequest($config->getUrl(), $id);
            $request['row']["IHREZ"] = $model->getId();

            $this->log->debug('Innova', ['r' => $request]);

            $this->updateRequest($model, $request);

            $requests[] = ['model' => $model];
        }

        // return $this->sendSapRequest($config->getUrl(), $requests);
        return true;
    }

    private function checkRtvRequestExists($id, $request)
    {
        return $this->checkRequestExists($id, $request, 'rtv');
    }

    public function checkFutureRequestExists($id, $request)
    {
        return $this->checkRequestExists($id, $request, 'futures');
    }

    /**
     * @return \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface[]
     */
    public function getRtvPendingToSend()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->sapInterfaceCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_FROM, ['eq' => 'rtv']);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_STATUS, ['eq' => \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_INIT]);
        $collection->load();

        return $collection->getItems();
    }

    /**
     * @param $id
     * @return false|\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface[]
     */
    public function getDocs($id, $from)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->sapInterfaceCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_REF, ['eq' => $id]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_FROM, ['eq' => $from]);
        $collection->load();

        if ($collection->count() == 0) {
            return false;
        }

        return $collection->getItems();
    }

    public function getTotalQty($items)
    {
        $total_qty = 0;

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface $item
         */
        foreach ($items as $item) {
            $request = $item->getRequest();
            $data = json_decode($request, true);

            foreach ($data['row']['ITEMS'] as $_item) {
                $total_qty += intval($_item['KWMENG']);
            }
        }

        return $total_qty;
    }

    private function checkRequestExists($id, $request, $from)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->sapInterfaceCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_REF, ['eq' => $id]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_FROM, ['eq' => $from]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_STATUS, ['in' => [
            \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_INIT,
            \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_SUCCESS
        ]]);
        $collection->load();

        if ($collection->count() == 0) {
            return false;
        }

        $items = $collection->getItems();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface $item
         */
        foreach ($items as $item) {
            $_request = $item->getRequest();
            if (!empty($_request)) {
                $_request = json_decode($_request, true);
                unset($_request['row']['IHREZ']);
                if ($from != 'rtv') {
                    unset($_request['row']['DATE']);
                }
                $_request = json_encode($_request);
                $json = json_encode($request);
                if ($_request == $json) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Southbay\ReturnProduct\Model\SouthbaySapInterface $model
     * @return bool
     */
    public function retry($model)
    {
        // return $this->sendSapRequest($model->getUrl(), [['model' => $model]]);
        $this->updateRequestStatus($model, __('Se va a reintentar retransmitir'), \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_INIT);
        return true;
    }

    public function sendSapRequest($url, $requests)
    {
        $config = $this->sapInterfaceConfigRepository->getConfigByUrl($url);

        if (is_null($config)) {
            return false;
        }

        $ok = true;
        $first = true;

        foreach ($requests as $item) {
            /**
             * @var \Southbay\ReturnProduct\Model\SouthbaySapInterface $model
             */
            $model = $item['model'];
            $this->log->debug('Sending new request', ['url' => $url, 'request' => $model->getRequest()]);

            if ($first) {
                $first = false;
            } else {
                sleep(5);
            }

            try {
                $curl = $this->getCurl();
                $this->setHeaders($curl, $config);

                // Enviar solicitud POST al API
                $curl->post($url, $model->getRequest());

                // Obtener la respuesta
                $response = $curl->getBody();

                $status = \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_ERROR;

                if ($this->checkRtvResponse($response, $model->getFrom(), $model->getRequest())) {
                    $status = \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_SUCCESS;
                }

                $this->updateRequestStatus($model, $response, $status);
            } catch (\Exception $e) {
                $this->log->error('Error sending request', ['request' => $model->getRequest(), 'error' => $e]);
                $this->updateRequestStatus($model, $e->getMessage(), \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_ERROR);
                $ok = false;
            }
        }

        return $ok;
    }

    private function checkRtvResponse($json, $ref = 'rtv', $request = null)
    {
        if ($ref == 'rtv') {
            $data = json_decode($json, true);

            if ($data === false) {
                $this->_logger->error('Error checking sap rtv response: ', [
                    'json' => $json,
                    'json_error_code' => json_last_error(),
                    'json_error_msg' => json_last_error_msg()
                ]);
                return false;
            }

            $data = array_first($data);
            if (isset($data['ET_OUTPUT'])) {
                if (isset($data['ET_OUTPUT']['item'])) {
                    if (isset($data['ET_OUTPUT']['item']['MESSAGE'])) {
                        $msg = trim(strtolower($data['ET_OUTPUT']['item']['MESSAGE']));
                        if ($msg == 'solicitud nc creada'
                            || $msg == 'documento de ventas asociado a referencia b2b'
                            || $msg == 'pedido creado') {
                            return true;
                        } else {
                            $this->_logger->error('Unrecognized message:', ['data' => $data]);
                        }
                    } else {
                        $this->_logger->error('Attr ET_OUTPUT.item.MESSAGE not exists:', ['data' => $data]);
                    }
                } else {
                    $this->_logger->error('Attr ET_OUTPUT.item not exists:', ['data' => $data]);
                }
            } else {
                $this->_logger->error('Attr ET_OUTPUT not exists:', ['data' => $data]);
            }
        } else {
            /*
            if (is_null($request)) {
                $this->_logger->error('The request cannot be verified', ['ref' => $ref, 'response' => $json]);
            } else if ($request == $json) {
                return true;
            } else {
                $this->_logger->error('The request not equal to response', ['ref' => $ref, 'request' => $request, 'response' => $json]);
            }
            */
            return true;
        }

        return false;
    }

    private function noInnovaUy($id, $no_innova_uy)
    {
        // $url = \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::SAP_ENDPOINT_NO_INNOVA;
        $config = $this->sapInterfaceConfigRepository->getConfigByType(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::TYPE_NO_INNOVA);

        if (is_null($config)) {
            return false;
        }

        $requests = [];
        $map = $this->mapCountryRepository->toMap();

        foreach ($no_innova_uy as $item) {
            $request = [
                "row" => [
                    "NOMBRE" => $item['invoice_ref'] . '-' . $item['invoice_date'],
                    "AUART" => "Z101",
                    "VKORG" => $map['UY'],
                    "VTWEG" => "20",
                    "SPART" => "01",
                    "KUNNR_AG" => $item['sold_to_code'],
                    "KUNNR_WE" => $item['ship_to_code'],
                    "AUGRU" => "903",
                    "BSTKD" => "B2B-#" . $id,
                    "TEXTO" => '',
                    "ITEMS" => []
                ]
            ];

            foreach ($item['items'] as $_item) {
                $request['row']['ITEMS'][] = [
                    "MATNR" => $_item['sku'],
                    "ARKTX" => substr($_item['sku'] . '-' . $_item['name'], 0, 40),
                    "BU" => $_item['bu'],
                    "KWMENG" => strval($_item['qty']),
                    "NETWR" => strval(round($_item['amount'], 2))
                ];
            }

            if ($this->checkRtvRequestExists($id, $request)) {
                continue;
            }

            $model = $this->createNewSapRequest($config->getUrl(), $id);
            $request['row']["IHREZ"] = $model->getId();

            $this->log->debug('UY No innova:', ['request' => $request]);

            $this->updateRequest($model, $request);

            $requests[] = ['model' => $model];
        }

        // return $this->sendSapRequest($config->getUrl(), $requests);
        return true;
    }

    private function noInnovaAr($id, $no_innova_ar, $no_innova_ar_max_date)
    {
        $config = $this->sapInterfaceConfigRepository->getConfigByType(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::TYPE_NO_INNOVA);

        if (is_null($config)) {
            return false;
        }

        $requests = [];
        $map = $this->mapCountryRepository->toMap();

        foreach ($no_innova_ar as $key => $item) {
            $str = date('Y-m-d', $no_innova_ar_max_date[$key]);
            $fecha_obj = new \DateTime($str);

            $first_date = $fecha_obj->modify('first day of this month')->format('d.m.Y');
            $last_date = $fecha_obj->modify('last day of this month')->format('d.m.Y');

            $request = [
                "row" => [
                    "NOMBRE" => "$first_date-$last_date",
                    "AUART" => "Z101",
                    "VKORG" => $map['AR'],
                    "VTWEG" => "20",
                    "SPART" => "01",
                    "KUNNR_AG" => $item['sold_to_code'],
                    "KUNNR_WE" => $item['ship_to_code'],
                    "AUGRU" => "903",
                    "BSTKD" => "B2B-#" . $id,
                    "TEXTO" => implode(',', $item['invoices']),
                    "ITEMS" => []
                ]
            ];

            foreach ($item['items'] as $_item) {
                $request['row']['ITEMS'][] = [
                    "MATNR" => $_item['sku'],
                    "ARKTX" => substr($_item['sku'] . '-' . $_item['name'], 0, 40),
                    "BU" => $_item['bu'],
                    "KWMENG" => strval($_item['qty']),
                    "NETWR" => strval(round($_item['amount'], 2))
                ];
            }

            if ($this->checkRtvRequestExists($id, $request)) {
                continue;
            }

            $model = $this->createNewSapRequest($config->getUrl(), $id);
            $request['row']["IHREZ"] = $model->getId();

            $this->updateRequest($model, $request);
            $requests[] = ['model' => $model];
        }

        // return $this->sendSapRequest($config->getUrl(), $requests);
        return true;
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Model\SouthbaySapInterface|null
     */
    public function findSapRequest($id)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->sapInterfaceCollectionFactory->create();
        return $collection->getItemById($id);
    }

    protected function createNewSapRequest($url, $id, $type = 'rtv')
    {
        /**
         * @var \Southbay\ReturnProduct\Model\SouthbaySapInterface $model
         */
        $model = $this->sapInterfaceFactory->create();
        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_INIT);
        $model->setFrom($type);
        $model->setRef($id);
        $model->setUrl($url);
        $model->setRequest('');
        $model->setResponse('');

        $this->sapInterfaceRepository->save($model);

        return $model;
    }

    /**
     * @param \Southbay\ReturnProduct\Model\SouthbaySapInterface $model
     * @param mixed $request
     * @return void
     */
    protected function updateRequest($model, $request)
    {
        $model->setRequest(json_encode($request));
        $this->sapInterfaceRepository->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Model\SouthbaySapInterface $model
     * @param mixed $request
     * @return void
     */
    public function updateRequestStatus($model, $response, $status)
    {
        $model->setStatus($status);
        if (is_null($response)) {
            $model->setResponse('');
        } else if (is_string($response)) {
            $model->setResponse($response);
        } else {
            $model->setResponse(json_encode($response));
        }

        if ($model->getStatus() == \Southbay\ReturnProduct\Model\SouthbaySapInterface::STATUS_ERROR) {
            $model->setEnd(true);
        }

        $this->sapInterfaceRepository->save($model);
    }

    public function checkNC($id)
    {
        $request = null;
        // $url = \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::SAP_ENDPOINT_CHECK;
        $config = $this->sapInterfaceConfigRepository->getConfigByType(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::TYPE_CHECK_STATUS);

        if (is_null($config)) {
            return;
        }

        try {
            $curl = $this->getCurl();
            $this->setHeaders($curl, $config);

            $request = [
                'IP_IHREZ' => strval($id)
            ];

            // Enviar solicitud POST al API
            $curl->post($config->getUrl(), json_encode($request));

            // Obtener la respuesta
            $response = $curl->getBody();

            $this->log->info('SAP Check status', ['id' => $id, 'response' => $response]);
            $this->saveSapCheckStatus($id, $response);

            $data = json_decode($response, true);

            if (!$data) {
                return;
            }

            $data = array_first($data);

            if (isset($data['ET_OUTPUT']['item'])) {
                $data = $data['ET_OUTPUT']['item'];
                $message = trim(strtolower($data['MESSAGE']));
                if ($message == 'nro. trámite contabilizado' || $message == 'nro. pedido contabilizado') {
                    $this->saveSapDoc($id, $data);
                }
            }

        } catch (\Exception $e) {
            $this->log->error('Error sending request', ['request' => $request, 'error' => $e]);
        }
    }

    public function setHeaders($curl, \Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig $config)
    {
        // $credentials = \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::SAP_USER . ':' . \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::SAP_PASS;

        $credentials = $config->getUsername() . ':' . $config->getPassword();
        $credentials = base64_encode($credentials);

        $curl->addHeader('Content-Type', 'application/json');
        $curl->addHeader('Authorization', "Basic $credentials");
    }

    public function saveSapDoc($id, $data)
    {
        $collection = $this->sapDocCollectionFactory->create();
        $collection->addFieldToFilter(SouthbaySapDoc::ENTITY_SAP_INTERFACE_ID, $id);

        if ($collection->count() > 0) {
            return;
        }

        $collection = $this->sapInterfaceCollectionFactory->create();
        $collection->addFieldToFilter(SouthbaySapInterface::ENTITY_ID, $id);

        if ($collection->count() == 0) {
            return;
        }

        $this->log->info('Start saving sap doc', ['id' => $id, 'data' => $data]);

        $conn = $this->sapDocRepository->getConnection();
        $conn->beginTransaction();

        try {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapDoc $doc
             */
            $doc = $this->sapDocFactory->create();
            $doc->setSapInterfaceId($id);
            $doc->setTypeDoc($data['VBTYP_VBAK']);
            $doc->setDocInternalNumber($data['VBELN_VBAK']);
            $doc->setDocLegalNumber($data['VBELN_VBRK']);
            $doc->setTotalNetAmount(floatval($data['NETWR']));
            $doc->setTotalAmount(floatval($data['VBRK_TOTAL']));

            $this->sapDocRepository->save($doc);

            $this->log->debug('new sap doc created', ['doc_id' => $doc->getId()]);

            /*
            $items = $data['ITEMS']['item'];
            if (isset($items['MATNR'])) {
                $items = [$items];
            }
            */

            $items = $data['ITEMS'];

            $this->log->info('sap doc items to save', ['items' => $items]);

            foreach ($items as $item) {
                /**
                 * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapDocItem $doc_item
                 */
                $doc_item = $this->sapDocItemFactory->create();
                $doc_item->setDocId(intval($doc->getId()));
                $doc_item->setSku(ltrim($item['MATNR'], '0'));
                $doc_item->setQty(floatval($item['FKIMG']));
                $doc_item->setPosition(ltrim($item['POSNR'], '0'));
                $doc_item->setNetAmount(floatval($item['NETWR']));
                $this->sapDocItemRepository->save($doc_item);
            }

            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->sapInterfaceCollectionFactory->create();
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface $model
             */
            $model = $collection->getItemById($id);
            $model->setEnd(true);
            $this->sapInterfaceRepository->save($model);

            $returnProductModel = $this->returnProductRepository->findById($model->getRef());
            $this->returnProductRepository->markAsClosed($returnProductModel);

            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    private function saveSapCheckStatus($id, $response)
    {
        $hash = hash('md5', $response);

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->southbaySapCheckStatusCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapCheckStatus::ENTITY_CHECK_SUM, $hash);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapCheckStatus::ENTITY_SAP_INTERFACE_ID, $id);
        if ($collection->count() == 0) {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapCheckStatus $model
             */
            $model = $this->sapCheckStatusFactory->create();
            $model->setSapInterfaceId($id);

            $model->setResponse($response);
            $model->setCheckSum($hash);

            $this->sapCheckStatusRepository->save($model);
        }
    }

    public function checkSapInterfacePendingToEnd()
    {
        $this->log->info('Start checkSapInterfacePendingToEnd...');
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->sapInterfaceCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_END, ['eq' => 0]);
        $total = $collection->count();

        if ($total > 0) {
            $this->log->info('checkSapInterfacePendingToEnd. Total fields to check', ['total' => $total]);
            $ids = $collection->getAllIds();
            foreach ($ids as $id) {
                $this->checkNC($id);
            }
        }
    }

    /**
     * @param $customer_code
     * @param $ship_to_code
     * @return \Southbay\CustomCustomer\Api\Data\ShipToInterface|null
     */
    public function findShipTo($customer_code, $ship_to_code, $invoice_sold_to_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->shipToCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToInterface::SOUTHBAY_SHIP_TO_CUSTOMER_CODE, $customer_code);
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToInterface::SOUTHBAY_SHIP_TO_CODE, $ship_to_code);

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->shipToMapCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToMapInterface::SOLD_TO_CODE, $customer_code);
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToMapInterface::SHIP_TO_OLD_CODE, $ship_to_code);

        if ($collection->count() == 0) {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->shipToMapCollectionFactory->create();
            $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToMapInterface::SOLD_TO_OLD_CODE, $invoice_sold_to_code);
            $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToMapInterface::SHIP_TO_OLD_CODE, $ship_to_code);

            if ($collection->count() == 0) {
                return null;
            }
        }

        /**
         * @var \Southbay\CustomCustomer\Api\Data\ShipToMapInterface $map
         */
        $map = $collection->getFirstItem();
        $ship_to_code = $map->getShipToCode();
        $customer_code = $map->getSoldToCode();

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->shipToCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToInterface::SOUTHBAY_SHIP_TO_CUSTOMER_CODE, $customer_code);
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToInterface::SOUTHBAY_SHIP_TO_CODE, $ship_to_code);

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @return \Magento\Framework\HTTP\Client\Curl
     */
    public function getCurl()
    {
        /**
         * @var \Magento\Framework\HTTP\Client\Curl $curl
         */
        $curl = new \Magento\Framework\HTTP\Client\Curl();
        $curl->setTimeout(500);

        return $curl;
    }

    public function getNewCurl($type, $request_body)
    {
        $config = $this->sapInterfaceConfigRepository->getConfigByType($type);

        if (is_null($config)) {
            $this->log->debug('Config not found', ['type' => $type]);
            return null;
        }

        $curl = $this->getCurl();
        $this->setHeaders($curl, $config);

        $curl->post($config->geturl(), json_encode($request_body));
        $response = $curl->getBody();

        if (empty($response)) {
            $this->log->debug('Empty response:', ['url' => $config->geturl(), 'request' => $request_body]);
            return null;
        }

        $response = json_decode($response, true);
        $data = array_first($response);

        if (isset($data['ET_OUT']['item'])) {
            return $data['ET_OUT']['item'];
        } else {
            $this->log->debug('Invalid response:', ['url' => $config->getUrl(), 'request' => $request_body, 'response' => $response]);
        }

        return null;
    }
}
