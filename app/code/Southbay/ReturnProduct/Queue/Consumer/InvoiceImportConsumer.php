<?php

namespace Southbay\ReturnProduct\Queue\Consumer;

use Magento\Framework\Exception\AlreadyExistsException;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoice as SouthbayIvoiceInterfase;
use Southbay\ReturnProduct\Model\Queue\InvoiceQueueMessage;

class InvoiceImportConsumer
{
    private $log;
    private $json;

    private $repository;
    private $item_repository;

    private $invoice_factory;
    private $invoice_item_factory;

    private $southbayInvoiceRepository;

    private $context;

    private $connectionResource;

    public function __construct(\Psr\Log\LoggerInterface                                              $log,
                                \Magento\Framework\Serialize\Serializer\Json                          $json,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice           $repository,
                                \Magento\Framework\Model\Context                                      $context,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceRepository $southbayInvoiceRepository,
                                \Magento\Framework\App\ResourceConnection                             $connectionResource,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem       $item_repository,
                                \Southbay\ReturnProduct\Model\SouthbayInvoiceFactory                  $invoice_factory,
                                \Southbay\ReturnProduct\Model\SouthbayInvoiceItemFactory              $invoice_item_factory)
    {
        $this->log = $log;
        $this->json = $json;
        $this->item_repository = $item_repository;
        $this->repository = $repository;
        $this->invoice_factory = $invoice_factory;
        $this->invoice_item_factory = $invoice_item_factory;
        $this->southbayInvoiceRepository = $southbayInvoiceRepository;
        $this->context = $context;
        $this->connectionResource = $connectionResource;
    }

    public function process($raw)
    {
        $message = $this->json->unserialize($raw);

        if (isset($message['invoice']) && $message['items']) {
            /**
             * @var \Magento\Framework\App\ObjectManager $objectManager
             */
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /**
             * @var \Psr\Log\LoggerInterface $log
             */
            $log = $objectManager->get('Psr\Log\LoggerInterface');

            /**
             * @var \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice $repository
             */
            $repository = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice');
            /**
             * @var \Southbay\ReturnProduct\Model\SouthbayInvoiceFactory $factory
             */
            $factory = $objectManager->get('Southbay\ReturnProduct\Model\SouthbayInvoiceFactory');

            /**
             * @var \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem $repository_item
             */
            $repository_item = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem');
            /**
             * @var \Southbay\ReturnProduct\Model\SouthbayInvoiceFactory $factory_item
             */
            $factory_item = $objectManager->get('Southbay\ReturnProduct\Model\SouthbayInvoiceItemFactory');

            /**
             * @var \Southbay\Product\Model\ResourceModel\SouthbaySapProduct\Collection $collection
             */
            $collection = $objectManager->get('Southbay\Product\Model\ResourceModel\SouthbaySapProduct\Collection');

            $last_invoice = $this->save($message['invoice'], $repository, $factory, $log);

            if (!is_null($last_invoice)) {
                foreach ($message['items'] as $item) {
                    $this->saveItem($item, $last_invoice, $repository_item, $factory_item, $collection, $log);
                }
            }
        }
    }

    private function save($item, $repository, $factory, $log)
    {
        try {
            $date = \DateTime::createFromFormat('d/m/Y', $item[5]);

            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoice $model
             */
            $model = $factory->create();
            $model->setCountryCode('A01P');
            $model->setOldInvoice(true);
            $model->setCustomerCode(ltrim($item[0], '0'));
            $model->setCustomerName($item[1]);
            $model->setCustomerShipToCode(ltrim($item[2], '0'));
            $model->setCustomerShipToName(trim($item[3]));
            $model->setInvoiceDate($date->format('Y-m-d'));
            $model->setIntInvoiceNum($item[6]);
            $model->setInvoiceRef($item[7]);

            $repository->save($model);

            return $model;
        } catch (\Exception $e) {
            $log->debug('Error saving invoice item:', ['item' => $item, 'error' => $e]);
        }

        return null;
    }

    /**
     * @param string $sku
     * @param string $size
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return \Southbay\Product\Api\Data\SouthbaySapProductInterface
     */
    private function findProduct($sku, $size, $collection)
    {
        $collection->addFieldToFilter(\Southbay\Product\Api\Data\SouthbaySapProductInterface::ENTITY_SKU, $sku);
        $collection->addFieldToFilter(\Southbay\Product\Api\Data\SouthbaySapProductInterface::ENTITY_SIZE, $size);
        $collection->addFieldToFilter(\Southbay\Product\Api\Data\SouthbaySapProductInterface::ENTITY_SAP_COUNTRY_CODE, 'A01P');
        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        } else {
            return null;
        }
    }

    private function saveItem($item, $last_invoice, $repository_item, $factory_item, $collection, $log)
    {
        try {
            $product = $this->findProduct($item[8], $item[10], $collection);

            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem $model
             */
            $model = $factory_item->create();
            $model->setInvoiceId($last_invoice->getId());
            $model->setSku($item[8]);
            $model->setSkuGeneric($product?->getSkuGeneric());
            $model->setSkuVariant($product?->getSkuVariant());
            $model->setBu($item[4]);
            $model->setName($item[9]);
            $model->setSize($item[10]);
            $model->setQty(intval($item[11]));
            $model->setAmount(floatval($item[12]));
            $model->setNetAmount(floatval($item[12]));
            $model->setUnitPrice(floatval($item[13]));
            $model->setNetUnitPrice(floatval($item[13]));

            $repository_item->save($model);
        } catch (\Exception $e) {
            $log->debug('Error saving invoice item:', ['item' => $item, 'error' => $e]);
        }
    }
}
