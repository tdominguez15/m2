<?php

namespace Southbay\ReturnProduct\Controller\NewReturn;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Helper\SouthbayCustomerHelper;
use Southbay\ReturnProduct\Controller\Base;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository as SouthbayReturnProductRepository;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository;
use Southbay\ReturnProduct\Model\SouthbayReturnProductFactory;
use Southbay\ReturnProduct\Model\SouthbayReturnProductItemFactory;
use Magento\Framework\App\ResourceConnection;

class Save extends Base
{
    private $resultJsonFactory;
    private $log;
    private $repository;
    private $repository_item;

    private $return_factory;
    private $return_item_factory;

    private $customerSession;

    private $resourceConnection;

    private $repository_invoice_item;

    private $messageManager;
    private $customerHelper;

    public function __construct(Context                                     $context,
                                LoggerInterface                             $log,
                                CustomerSession                             $customerSession,
                                SouthbayCustomerHelper                      $customerHelper,
                                SouthbayReturnProductFactory                $return_factory,
                                SouthbayReturnProductItemFactory            $return_item_factory,
                                SouthbayReturnProductRepository             $repository,
                                SouthbayReturnProductItemRepository         $repository_item,
                                SouthbayInvoiceItemRepository               $repository_invoice_item,
                                JsonFactory                                 $resultJsonFactory,
                                ResourceConnection                          $resourceConnection,
                                \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        parent::__construct($context, $messageManager);
        $this->log = $log;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->repository = $repository;
        $this->repository_item = $repository_item;
        $this->return_factory = $return_factory;
        $this->return_item_factory = $return_item_factory;
        $this->customerSession = $customerSession;
        $this->resourceConnection = $resourceConnection;
        $this->repository_invoice_item = $repository_invoice_item;
        $this->messageManager = $messageManager;
        $this->customerHelper = $customerHelper;
    }

    public function execute()
    {
        $southbay_return_products = $this->context->getRequest()->getParam('southbay_return_product');
        $southbay_return_product_reason = $this->context->getRequest()->getParam('southbay_return_product_reason');
        $southbay_return_product_reason_text = $this->context->getRequest()->getParam('southbay_return_product_reason_text');
        $southbay_return_type = $this->context->getRequest()->getParam('type');
        $sold_to_id = $this->context->getRequest()->getParam('sold_to_id');

        $this->log->info('Init return product request...', [
            'type' => $southbay_return_type,
            'products' => $southbay_return_products,
            'southbay_return_product_reason' => $southbay_return_product_reason,
            'southbay_return_product_reason_text' => $southbay_return_product_reason_text
        ]);

        $connection = $this->resourceConnection->getConnection();

        $process_id = null;
        $process_status = null;
        $process_message = '';
        $map_balance = [];
        $start_transaction = false;
        $sold_to = null;
        $email = $this->customerSession->getCustomer()->getEmail();

        try {
            if (empty($sold_to_id)) {
                $process_status = 'error';
                $process_message = __('No indico el solicitante');
            } else {
                $sold_to = $this->customerHelper->getSoldToById($email, $sold_to_id);

                if (is_null($sold_to)) {
                    $process_status = 'error';
                    $process_message = __('No existe el solicitante');
                }
            }

            if ($process_status != 'error' && empty($southbay_return_products)) {
                $process_status = 'error';
                $process_message = __('Debe indicar que items quiere devolver');
            } else if ($process_status != 'error') {
                $total_qty = 0;
                $_southbay_return_products = [];
                foreach ($southbay_return_products as $key => $qty) {
                    $invoice_item = $this->repository_invoice_item->findById($key);

                    if ($invoice_item === false) {
                        $this->log->error('Invoice item id not found', ['invoice_item_id' => $key]);
                        $process_status = 'error';
                        $process_message = __('No se encontro la factura asociada a uno de los items');
                        break;
                    }

                    $qty = intval($qty);
                    if ($qty > 0) {
                        $total_qty += $qty;
                        $_southbay_return_products[$key] = $qty;

                        if (!isset($map_balance[$invoice_item->getId()])) {
                            $balance = $this->repository_item->findBalanceByInvoiceItemId($invoice_item->getId());

                            if (is_null($balance)) {
                                $map_balance[$invoice_item->getId()] = $invoice_item->getQty();
                            } else {
                                $map_balance[$invoice_item->getId()] = $balance->getTotalAvailable();
                            }
                        }

                        if ($qty <= $map_balance[$invoice_item->getId()]) {
                            $map_balance[$invoice_item->getId()] = $map_balance[$invoice_item->getId()] - $qty;
                        } else {
                            $this->log->error('Invoice item without total available',
                                [
                                    'invoice_item_id' => $key,
                                    'total_available' => $map_balance[$invoice_item->getId()],
                                    'total_return' => $qty
                                ]);
                            $process_status = 'error';
                            $process_message = __('El sku ' . $invoice_item->getSku() . ' no cuenta con la cantidad de unidades disponibles que quiere devolver');
                            break;
                        }
                    } else {
                        $process_status = 'error';
                        $process_message = __('No ingresó que cantidad de unidades quiere devolver para el SKU ') . $invoice_item->getSku();
                        break;
                    }
                }

                if ($process_status != 'error') {
                    $southbay_return_products = $_southbay_return_products;
                    if ($total_qty == 0) {
                        $process_status = 'error';
                        $process_message = __('Debe indicar que cantidad de unidades quiere devolver');
                    } else if ($southbay_return_type == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL) {
                        if (empty($southbay_return_product_reason_text)) {

                        }
                        if (!empty($southbay_return_product_reason)) {
                            $keys = array_keys($southbay_return_products);
                            foreach ($keys as $key) {
                                if (isset($southbay_return_product_reason[$key])) {
                                    $codes = $southbay_return_product_reason[$key];
                                    // if (empty($codes)) {
                                    // $process_status = 'error';
                                    // break;
                                    // }
                                } else {
                                    // $process_status = 'error';
                                    // break;
                                }
                            }
                        } else {
                            // $process_status = 'error';
                        }

                        // if ($process_status == 'error') {
                        // $process_message = __('No indico el motivo de la devolución para alguno de los items');
                        // }
                    }
                }
            }

            if ($process_status != 'error') {
                $start_transaction = true;
                $connection->beginTransaction();
                $process_id = $this->saveAll($sold_to, $southbay_return_type, $southbay_return_products, $southbay_return_product_reason, $southbay_return_product_reason_text);
                $process_status = 'success';

                $connection->commit();
            }
        } catch (\Exception $e) {
            $this->log->error('Error keeping return product request', ['e' => $e]);
            if ($start_transaction) {
                $connection->rollBack();
            }
            $process_id = null;
            $process_status = 'error';
            $process_message = __('Ocurrio un error inesperado guardando los datos');
        }

        $response = [
            'status' => $process_status,
            'id' => $process_id,
            'message' => $process_message
        ];

        $this->log->info('End return product request', ['response' => $response]);

        return $this->resultJsonFactory->create()->setData(
            $response
        );
    }

    private function saveAll($sold_to, $southbay_return_type, $southbay_return_products, $southbay_return_product_reason, $southbay_return_product_reason_text)
    {
        $total_qty = 0;
        $total_amount = 0;

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $header
         */
        $header = $this->return_factory->create();
        $header->setType($southbay_return_type);
        $header->setCountryCode($sold_to->getCountryCode());
        $header->setCustomerCode($sold_to->getCustomerCode());
        $header->setCustomerName($sold_to->getCustomerName());
        $header->setUserCode($this->customerSession->getCustomerId());
        $header->setUserName($this->customerSession->getCustomer()->getName());
        if ($southbay_return_type == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD) {
            $header->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT);
            $header->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_GOOD_INIT);
        } else if ($southbay_return_type == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL) {
            $header->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_FAIL_INIT);
            $header->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_FAIL_INIT);
        }
        $header->setTotalReturn($total_qty);
        $header->setTotalAmount($total_amount);
        $header->setTotalAccepted(0);
        $header->setTotalAmountAccepted(0);
        $header->setTotalRejected(0);
        $header->setTotalAmountRejected(0);
        $header->setPrinted(false);
        $header->setPrintedAt(null);
        $header->setLabelTotalPackages(0);
        $this->repository->save($header);

        foreach ($southbay_return_products as $key => $qty) {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem $invoice_item
             */
            $invoice_item = $this->repository_invoice_item->findById($key);

            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem $item
             */
            $item = $this->return_item_factory->create();
            $item->setReturnId($header->getId());
            $item->setInvoiceItemId($invoice_item->getId());
            $item->setInvoiceId($invoice_item->getInvoiceId());
            $item->setSku($invoice_item->getSku());
            $item->setSku2($invoice_item->getSkuVariant());
            $item->setName($invoice_item->getName());
            $item->setSize($invoice_item->getSize());
            $item->setQty($qty);
            $item->setNetUnitPrice($invoice_item->getNetUnitPrice());
            $item->setNetAmount($invoice_item->getNetUnitPrice() * $qty);

            if ($header->getType() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD) {
                $item->setReasonsCode(null);
                $item->setReasonsText(null);
            } else {
                if (isset($southbay_return_product_reason[$key])) {
                    $item->setReasonsCode($southbay_return_product_reason[$key]);
                } else {
                    $item->setReasonsCode(null);
                }
                if(isset($southbay_return_product_reason_text[$key])) {
                    $item->setReasonsText($southbay_return_product_reason_text[$key]);
                } else {
                    $item->setReasonsText(null);
                }
            }

            $item->setQtyAccepted(0);
            $item->setQtyRejected(0);
            $item->setAmountAccepted(0);

            $this->repository_item->save($item, $invoice_item);

            $total_qty += $item->getQty();
            $total_amount += $item->getNetAmount();
        }

        $header->setTotalReturn($total_qty);
        $header->setTotalAmount($total_amount);

        $this->repository->save($header);

        return $header->getId();
    }
}
