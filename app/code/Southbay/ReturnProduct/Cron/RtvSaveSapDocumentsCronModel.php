<?php

namespace Southbay\ReturnProduct\Cron;

use Southbay\Product\Setup\Patch\Data\SapOrderStatusPatch;
use Southbay\ReturnProduct\Api\Data\SouthbaySapDocInterface;
use Southbay\ReturnProduct\Api\Data\SouthbaySapInterface;
use Southbay\ReturnProduct\Helper\SendSapRtvRequest;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapInterfaceCollectionFactory;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocInterface as ResourceModel;
use Southbay\ReturnProduct\Model\SouthbaySapDocInterface as Model;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocInterface\CollectionFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class RtvSaveSapDocumentsCronModel
{
    private $repository;
    private $collectionFactory;

    private $log;

    private $sapRtvRequest;

    private $urlBuilder;

    private $storeManager;

    private $scopeConfig;

    private $sapInterfaceCollectionFactory;

    private $sapInterfaceRepository;

    private $orderCollectionFactory;

    public function __construct(ResourceModel                                                    $repository,
                                \Psr\Log\LoggerInterface                                         $log,
                                \Magento\Framework\UrlInterface                                  $urlBuilder,
                                \Magento\Store\Model\StoreManagerInterface                       $storeManager,
                                SendSapRtvRequest                                                $sapRtvRequest,
                                \Magento\Framework\App\Config\ScopeConfigInterface               $scopeConfig,
                                SouthbaySapInterfaceCollectionFactory                            $sapInterfaceCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterface $sapInterfaceRepository,
                                CollectionFactory                                                $collectionFactory,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory       $orderCollectionFactory)
    {
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->sapRtvRequest = $sapRtvRequest;
        $this->urlBuilder = $urlBuilder;
        $this->log = $log;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->sapInterfaceCollectionFactory = $sapInterfaceCollectionFactory;
        $this->sapInterfaceRepository = $sapInterfaceRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function execute()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', 'ok');
        $collection->addFieldToFilter('start_import_date', ['null' => true]);
        $collection->load();

        $items = $collection->getItems();

        $this->log->debug('Total items to save', ['c' => count($items)]);

        /**
         * @var Model $item
         */
        foreach ($items as $item) {
            $this->process($item);
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('retry_at', ['lteq' => date('Y-m-d H:i:s')]);
        $collection->addFieldToFilter('status', 'error_saving');
        $collection->load();

        $items = $collection->getItems();

        $this->log->debug('Total items to retry', ['c' => count($items)]);

        /**
         * @var Model $item
         */
        foreach ($items as $item) {
            $this->process($item);
        }
    }

    private function process(Model $model)
    {
        $model->setStatus('processing');
        $model->setStartImportDate(date('Y-m-d H:i:s'));
        $model->setEndImportDate(null);
        $model->setRetryAt(null);
        $this->repository->save($model);

        $status = 'complete';
        $msg = 'documentos incorporados';

        try {
            $data = json_decode($model->getRawData(), true);
            if ($model->getType() == 'rtv') {
                if (empty($data) || empty($data['documentos']) || empty($data['items'])) {
                    $status = 'invalid';
                    $msg = 'Invalid data';
                } else {
                    $result = $this->saveRtvDoc($data, $model);
                    $msg = $result['msg'];
                    $status = $result['status'];
                }
            } else if ($model->getType() == 'order_entry') {
                if (empty($data)) {
                    $status = 'invalid';
                    $msg = 'Invalid data';
                } else {
                    $result = $this->saveOrderEntry($data);
                    $msg = $result['msg'];
                    $status = $result['status'];
                }
            }
        } catch (\Exception $e) {
            $this->log->error('Error processing documents: ', ['error' => $e]);
            $status = 'fail';
            $msg = 'Unexpected error: ' . $e->getMessage();
        }

        $model->setEndImportDate(date('Y-m-d H:i:s'));
        $model->setResultMsg($msg);
        $model->setStatus($status);
        $this->repository->save($model);
    }

    private function saveOrderEntry($rows)
    {
        $status = 'complete';
        $msg = '';

        foreach ($rows as $row) {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->sapInterfaceCollectionFactory->create();
            /**
             * @var SouthbaySapInterface $doc
             */
            $doc = $collection->getItemById($row['IHREZ']);

            if (is_null($doc)) {
                $this->log->error('Sap document not found', ['id' => $row['IHREZ'], 'row' => $row]);
                $status = 'partial-data';
                $msg = 'Sap document not found ' . $row['IHREZ'];
            } else {
                $doc->setResponse(json_encode($row));
                $doc->setEnd(true);

                if (empty($row['VBELN'])) {
                    $doc->setStatus('error');
                }

                $this->sapInterfaceRepository->save($doc);

                $order = $this->findOrder($doc->getRef());

                if (is_null($order)) {
                    $this->log->error('Order not found', ['order_id' => $doc->getRef(), 'row' => $row]);
                    $status = 'partial-data';
                    $msg = 'Order not found ' . $doc->getRef();
                } else {
                    if ($doc->getStatus() == 'error') {
                        $order->addStatusHistoryComment(__('Error generando orden de compra: %1. IHREZ: %2', $row['MESSAGE'] ?? __('Error desconocido'), $row['IHREZ']));
                    } else {
                        $order->addStatusHistoryComment(__('Orden de compra generada: %1. IHREZ: %2', ltrim($row['VBELN'], '0'), $row['IHREZ']));
                    }

                    if ($order->getStatus() == SapOrderStatusPatch::STATUS_SEND_TO_SAP) {
                        if ($doc->getStatus() == 'error') {
                            $order->setStatus(SapOrderStatusPatch::STATUS_CONFIRM_FAIL);
                        } else {
                            $order->setStatus(SapOrderStatusPatch::STATUS_CONFIRM);
                        }
                    } else if ($order->getStatus() == SapOrderStatusPatch::STATUS_CONFIRM && $doc->getStatus() == 'error') {
                        $order->setStatus(SapOrderStatusPatch::STATUS_CONFIRM_FAIL);
                    }

                    $order->save();
                }
            }
        }

        if ($status == 'complete' && empty($msg)) {
            $msg = __('Documentos incorporados');
        }

        return [
            'status' => $status,
            'msg' => $msg
        ];
    }

    private function saveRtvDoc($data, $model)
    {
        $status = 'complete';
        $msg = '';
        $error = false;

        $invoices = [];
        $rtv_docs = [];

        $items_to_send = [];

        if (isset($data['items']['MATNR'])) {
            $data['items'] = [$data['items']];
        }

        foreach ($data['items'] as $item) {
            if (!isset($items_to_send[$item['VBELN_VBRK']])) {
                $items_to_send[$item['VBELN_VBRK']] = [];
            }
            $items_to_send[$item['VBELN_VBRK']][] = $item;
        }

        if (isset($data['documentos']['VBTYP_VBRK'])) {
            $data['documentos'] = [$data['documentos']];
        }

        foreach ($data['documentos'] as $doc) {
            if (!isset($items_to_send[$doc['VBELN_VBRK']])) {
                $status = 'partial-data';
                $msg = 'Document without item: ' . $doc['VBELN_VBRK'];
                $error = true;
                break;
            }

            $items = $items_to_send[$doc['VBELN_VBRK']];

            // VBTYP_VBRK = Tipo de Documento (VBRK-VBTYP): "M" (Factura), "O" (Nota de Crédito)
            if ($doc['VBTYP_VBRK'] == 'M') {
                $date = \Datetime::createFromFormat('Ymd', $doc['FKDAT']);

                if (!$date) {
                    $status = 'partial-data';
                    $msg = 'Document without valid date: ' . $doc['VBELN_VBRK'];
                    $error = true;
                    break;
                }

                if (!isset($doc['KUNNR_AG']) || empty($doc['KUNNR_WE'])) {
                    $status = 'partial-data';
                    $msg = 'Document without sold to or ship to: ' . $doc['VBELN_VBRK'];
                    $error = true;
                    break;
                }

                $invoice = [
                    'southbay_invoice_country_code' => $doc['VKORG'], // Pais (A01P - Argentina; B01P Uruguay)
                    'southbay_old_invoice' => false,                  // Indica si es una factura vieja (esto siempre va a ser false)
                    'southbay_customer_code' => $doc['KUNNR_AG'],     // Codigo del sold to
                    'southbay_customer_ship_to_code' => $doc['KUNNR_WE'], // Codigo de la puerta
                    'southbay_invoice_date' => $date->format('Y-m-d'),                      // Fecha de facturacion
                    'southbay_int_invoice_num' => $doc['VBELN_VBRK'],   // Nro de factura interno
                    'southbay_invoice_ref' => $doc['XBLNR'] // Nro legal
                ];


                $invoice_items = [];

                foreach ($items as $_item) {
                    $qty = intval(trim($_item['FKMG']));
                    $amount = floatval(trim($_item['NETWR']));
                    $unit_price = $amount / $qty;
                    $invoice_items[] = [
                        'southbay_invoice_item_sku_variant' => intval(trim($_item['MATNR'])),
                        'southbay_invoice_item_position' => $_item['POSNR'],
                        'southbay_invoice_item_qty' => $qty,
                        'southbay_invoice_item_name' => $_item['ARKTX'],
                        'southbay_invoice_item_amount' => $amount,
                        'southbay_invoice_item_net_amount' => $amount,
                        'southbay_invoice_item_unit_price' => $unit_price,
                        'southbay_invoice_net_item_unit_price' => $unit_price
                    ];
                }

                $invoices[] = [
                    'head' => $invoice,
                    'items' => $invoice_items
                ];
            } elseif ($doc['VBTYP_VBRK'] == 'O') {
                if (!isset($doc['VBTYP_VBRK'])) {
                    $status = 'partial-data';
                    $msg = 'Document without b2b reference: ' . $doc['VBELN_VBRK'];
                    $error = true;
                    break;
                }

                $rtv = [
                    'VBTYP_VBAK' => $doc['VBTYP_VBAK'],
                    'VBELN_VBAK' => $doc['VBELN_VBRK'],
                    'VBELN_VBRK' => $doc['XBLNR'],
                    'NETWR' => floatval(trim($doc['NETWR'])),
                    'VBRK_TOTAL' => floatval(trim($doc['VBRK_TOTAL'])),
                    'ITEMS' => []
                ];

                $rtv_items = [];

                foreach ($items as $_item) {
                    $rtv_items[] = [
                        'MATNR' => trim($_item['MATNR']),
                        'FKIMG' => trim($_item['FKMG']),
                        'POSNR' => $_item['POSNR'],
                        'NETWR' => trim($_item['NETWR'])
                    ];
                }

                $rtv['ITEMS'] = $rtv_items;

                $rtv_docs[] = [
                    'tool_doc_id' => $doc['IHREZ'],
                    'rtv' => $rtv
                ];
            } else {
                $this->log->warning('Unknown document type: ' . $doc['VBTYP_VBRK']);
            }
        }

        if (!$error) {
            if (!empty($rtv_docs)) {
                foreach ($rtv_docs as $data) {
                    $this->sapRtvRequest->saveSapDoc($data['tool_doc_id'], $data['rtv']);
                }
            }

            if (!empty($invoices)) {
                if (!$this->sendInvoices($invoices)) {
                    $status = 'error_saving';
                    $msg = 'Error saving invoices';

                    $retry_at = new \DateTime();
                    $retry_at->modify('+10 minutes');

                    $model->setRetryAt($retry_at->format('Y-m-d H:i:s'));
                }
            }
        }

        return [
            'status' => $status,
            'msg' => $msg
        ];
    }

    private function sendInvoices($invoices)
    {
        /*
        // $store = $this->storeManager->getStore();
        // $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, true);

        // Util solo para entornos locales de prueba/desarrollo
        $baseUrl = getenv('LOCAL_API_URL');

        if (!$baseUrl) {
            $baseUrl = 'http://localhost';
        }
        */

        $url_base = $this->scopeConfig->getValue('southbay_magento/general/url_base');
        $url = $url_base . '/rest/V1/southbay/sap/invoice';

        $promises = [];
        $client = new Client([
            'timeout' => 60,
            'verify' => false
        ]);

        foreach ($invoices as $invoice) {
            $promises[] = $client->postAsync($url, ['json' => ['data' => $invoice]]);
        }

        $responses = Promise\Utils::settle($promises)->wait();
        $total = count($invoices);
        $total_ok = 0;

        foreach ($responses as $response) {
            if ($response['state'] === 'fulfilled') {
                $json = $response['value']->getBody()->getContents();
                $response = json_decode($json, true);
                if ($response && isset($response['return']['estado']) && $response['return']['estado'] == 'ok') {
                    $total_ok++;
                } else {
                    $this->log->error('error saving invoice', ['response' => $response]);
                }
            } else {
                $this->log->error('error sending request', ['response' => $response]);
            }
        }

        return ($total == $total_ok);
    }

    /**
     * @param $id
     * @return \Magento\Sales\Model\Order|null
     */
    private function findOrder($id)
    {
        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection
         */
        $collection = $this->orderCollectionFactory->create();
        return $collection->getItemById($id);
    }
}
