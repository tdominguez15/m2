<?php

namespace Southbay\ApproveOrders\Cron;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Southbay\CustomCheckout\Model\SapInterface\SapOrderEntryFutureNotification;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Model\ShipToRepository;
use Southbay\CustomCustomer\Model\SoldToRepository;
use Southbay\Product\Setup\Patch\Data\SapOrderStatusPatch;
use Magento\Sales\Api\ShipOrderInterface;

class SendOrdersToSap
{

    private $log;
    private $sender;

    private $configStoreRepository;

    private $collectionFactory;

    private $approveOrderHelper;

    private $soldToRepository;

    private $invoiceService;
    private $invoiceSender;
    private $transaction;

    private $shipToRepository;

    private $shipOrderInterface;

    public function __construct(LoggerInterface                                            $log,
                                ConfigStoreRepository                                      $configStoreRepository,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
                                SouthbayApproveOrderHelper                                 $approveOrderHelper,
                                SapOrderEntryFutureNotification                            $sender,
                                SoldToRepository                                           $soldToRepository,
                                ShipToRepository                                           $shipToRepository,
                                \Magento\Sales\Model\Service\InvoiceService                $invoiceService,
                                \Magento\Sales\Model\Order\Email\Sender\InvoiceSender      $invoiceSender,
                                ShipOrderInterface                                         $shipOrderInterface,
                                \Magento\Framework\DB\Transaction                          $transaction)
    {
        $this->log = $log;
        $this->sender = $sender;
        $this->configStoreRepository = $configStoreRepository;
        $this->collectionFactory = $collectionFactory;
        $this->approveOrderHelper = $approveOrderHelper;
        $this->soldToRepository = $soldToRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->shipOrderInterface = $shipOrderInterface;
        $this->transaction = $transaction;
        $this->shipToRepository = $shipToRepository;
    }

    public function sendAtOnce()
    {
        $this->sendByFunctionCode(ConfigStoreInterface::FUNCTION_CODE_AT_ONCE);
    }

    public function sendFuture()
    {
        $this->sendByFunctionCode(ConfigStoreInterface::FUNCTION_CODE_FUTURES);
    }

    private function sendByFunctionCode($code)
    {
        $this->log->info("Start sending orders for: $code");

        $config_stores = $this->configStoreRepository->findByFunctionCode($code);

        foreach ($config_stores as $config_store) {
            $orders = $this->getOrders($config_store->getSouthbayStoreCode());

            /**
             * @var \Magento\Sales\Model\Order $order
             */
            foreach ($orders as $order) {
                try {
                    $this->log->info('Start sending order',
                        [
                            'order_id' => $order->getId(),
                            'store_id' => $config_store->getSouthbayStoreCode(),
                            'sold_to_id' => $order->getExtCustomerId()
                        ]
                    );

                    $sold_to = $this->soldToRepository->getById($order->getExtCustomerId());
                    $is_internal = false;
                    $comment = '';

                    if (is_null($sold_to)) {
                        $order->addCommentToStatusHistory(__('No existe el solicitante'));
                        $order->setStatus(SapOrderStatusPatch::STATUS_SEND_ERROR);
                        $order->save();
                        continue;
                    } else if ($sold_to->getIsInternal()) {
                        $is_internal = true;
                        $comment = __('El solicitante es interno. No se envia la orden a SAP');
                    } else {
                        $ship_to = $this->shipToRepository->getById($order->getBillingAddress()->getVatId());
                        if (is_null($ship_to)) {
                            $order->addCommentToStatusHistory(__('No existe la puerta de destino'));
                            $order->setStatus(SapOrderStatusPatch::STATUS_SEND_ERROR);
                            $order->save();
                            continue;
                        } else if ($ship_to->getIsInternal()) {
                            $is_internal = true;
                            $comment = __('La puerta de destino es interna. No se envia la orden a SAP');
                        }
                    }

                    if ($code == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
                        $ship = $this->shipOrder($order);

                        if (!$ship) {
                            $order->addCommentToStatusHistory(__('No fue posible marcar la orden como lista para enviar'));
                            $order->setStatus(SapOrderStatusPatch::STATUS_CONFIRM_FAIL);
                            $order->save();
                            continue;
                        }
                    }

                    if ($is_internal) {
                        $order->addCommentToStatusHistory($comment);

                        if ($order->canInvoice()) {
                            $invoice = $this->invoiceService->prepareInvoice($order);
                            $invoice->register();
                            $invoice->save();
                            $transactionSave = $this->transaction->addObject(
                                $invoice
                            )->addObject(
                                $invoice->getOrder()
                            );
                            $transactionSave->save();
                            $this->invoiceSender->send($invoice);
                        } else {
                            $order->setStatus(SapOrderStatusPatch::STATUS_SEND_ERROR);
                            $order->addCommentToStatusHistory(__('No fue posible dar por confirmada la orden'));
                        }

                        $order->save();
                        continue;
                    }

                    $result = $this->sender->sendOrder($order, $config_store);


                    if ($result) {
                        $this->log->info('Order sent', [
                            'order_id' => $order->getId(),
                            'store_id' => $config_store->getSouthbayStoreCode(),
                            'total_docs' => $result
                        ]);

                        $detail = $this->sender->getDetail();
                        $total_fail = 0;
                        $total_send = 0;

                        foreach ($detail as $item) {
                            if ($item['status'] == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_ERROR) {
                                if (!$item['end']) {
                                    $total_fail++;
                                    $total_send++;
                                    $order->addStatusHistoryComment(__('Error enviando pedido de venta: %1', $item['id']));
                                }
                            } else if ($item['status'] == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_SUCCESS) {
                                if (!$item['end']) {
                                    $total_send++;
                                    $order->addStatusHistoryComment(__('Se envio el pedido de venta: %1', $item['id']));
                                }
                            } else {
                                $total_fail++;
                                $order->addStatusHistoryComment(__('Error el pedido de venta %1 no tiene un estado valido', $item['id']));
                            }
                        }

                        $order->addStatusHistoryComment(__('Orden enviada a sap. Total de pedidos de ventas: %1', $total_send));

                        if ($total_fail > 0) {
                            $order->setStatus(SapOrderStatusPatch::STATUS_SEND_ERROR);
                        } else {
                            $order->setStatus(SapOrderStatusPatch::STATUS_SEND_TO_SAP);
                        }
                    } else {
                        $this->log->error('Error sending order', ['order_id' => $order->getId(), 'store_id' => $config_store->getSouthbayStoreCode()]);

                        $order->addStatusHistoryComment(__('Error enviado pedidos de venta'));
                        $order->setStatus(SapOrderStatusPatch::STATUS_SEND_ERROR);
                    }
                    $order->save();
                } catch (\Exception $e) {
                    $this->log->error('Error sending order:', [
                        'order_id' => $order->getId(),
                        'store_id' => $config_store->getSouthbayStoreCode(),
                        'e' => $e
                    ]);
                }
            }
        }

        $this->log->info("End sending orders for: $code");
    }

    private function getOrders($store_id)
    {
        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('store_id', ['eq' => $store_id]);
        $collection->addFieldToFilter('status', ['eq' => Order::STATE_PROCESSING]);
        $collection->load();

        return $collection->getItems();
    }

    public function shipOrder(\Magento\Sales\Model\Order $order)
    {
        $result = false;
        try {
            if ($order->canShip()) {
                $id = $this->shipOrderInterface->execute(
                    $order->getId(),
                    [],
                    false,
                    false,
                    null,
                    [],
                    [],
                    null
                );

                $this->log->info('Shipped order #' . $order->getId(), ['result_id' => $id]);
            }

            $result = true;
        } catch (\Exception $e) {
            $this->log->error('Error making ship order:', ['error' => $e]);
        }

        return $result;
    }
}
