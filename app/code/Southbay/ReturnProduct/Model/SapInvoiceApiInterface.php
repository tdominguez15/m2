<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SapDocApiResponseInterface;
use Southbay\ReturnProduct\Model\SouthbayInvoiceFactory as ModelFactory;
use Southbay\ReturnProduct\Model\SouthbayInvoiceItemFactory as ItemModelFactory;
use Southbay\ReturnProduct\Api\SapInvoiceApiInterface as ApiInterface;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice as ResourceModel;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem as ItemResourceModel;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem\CollectionFactory as ItemCollectionFactory;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice\CollectionFactory as CollectionFactory;

use Southbay\ReturnProduct\Api\Data\SouthbayInvoice as SouthbayIvoiceInterfase;

class SapInvoiceApiInterface implements ApiInterface
{
    private $log;
    private $repository;
    private $factory;
    private $item_factory;
    private $item_repository;

    private $itemCollectionFactory;
    private $collectionFactory;

    private $resourceConnection;

    private $productCollectionFactory;

    private $soldToCollectionFactory;
    private $shipToCollectionFactory;

    public function __construct(\Psr\Log\LoggerInterface                                                   $log,
                                ResourceModel                                                              $repository,
                                ItemResourceModel                                                          $item_repository,
                                ModelFactory                                                               $factory,
                                ItemModelFactory                                                           $item_factory,
                                ItemCollectionFactory                                                      $itemCollectionFactory,
                                \Magento\Framework\App\ResourceConnection                                  $resourceConnection,
                                \Southbay\Product\Model\ResourceModel\SouthbaySapProduct\CollectionFactory $productCollectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory      $soldToCollectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory      $shipToCollectionFactory,
                                CollectionFactory                                                          $collectionFactory)
    {
        $this->log = $log;
        $this->factory = $factory;
        $this->item_factory = $item_factory;
        $this->repository = $repository;
        $this->item_repository = $item_repository;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->soldToCollectionFactory = $soldToCollectionFactory;
        $this->shipToCollectionFactory = $shipToCollectionFactory;
    }

    /**
     * @param $data
     * @return SapDocApiResponseInterface
     */
    public function save($data): SapDocApiResponseInterface
    {
        $status = '';
        $msg = '';

        $conn = $this->resourceConnection->getConnection();
        $init_transaction = false;

        try {
            $head = $data['head'];
            $items = $data['items'];

            /**
             * @var \Southbay\ReturnProduct\Model\SouthbayInvoice $invoice
             */
            $invoice = $this->factory->create(['data' => $head]);

            $sold_to = $this->findSolTo($invoice->getCustomerCode(), $invoice->getCountryCode());

            if (!is_null($sold_to)) {
                $invoice->setCustomerName($sold_to->getCustomerName());
                $ship_to = $this->findshipTo($invoice->getCustomerShipToCode(), $invoice->getCustomerCode());
                if (!is_null($ship_to)) {
                    $invoice->setCustomerShipToName($ship_to->getName());
                } else {
                    $invoice->setCustomerShipToName('N/A');
                }
            } else {
                $invoice->setCustomerName('N/A');
                $invoice->setCustomerShipToName('N/A');
            }

            if (!$this->invoiceExists($invoice)) {
                $conn->beginTransaction();
                $init_transaction = true;
                $this->repository->save($invoice);

                $id = $invoice->getId();

                foreach ($items as $item) {
                    /**
                     * @var \Southbay\ReturnProduct\Model\SouthbayInvoiceItem $invoice
                     */
                    $item_invoice = $this->item_factory->create(['data' => $item]);
                    $item_invoice->setInvoiceId($id);
                    $item_invoice->setSkuGeneric(substr($item_invoice->getSkuVariant(), 0, 8));

                    $product = $this->findProduct($item_invoice->getSkuVariant(), $invoice->getCountryCode());

                    if (!is_null($product)) {
                        $item_invoice->setSku($product->getSku());
                        $item_invoice->setSku2($product->getSku() . '/' . $product->getSize());
                        $item_invoice->setBu($product->getBu());
                        $item_invoice->setSize($product->getSize());
                    }

                    $this->item_repository->save($item_invoice);
                }

                $msg = 'factura creada. total items: ' . count($items);
                $conn->commit();
            } else {
                $msg = 'ya existe';
            }

            $status = 'ok';
        } catch (\Exception $e) {
            if ($init_transaction) {
                $conn->rollBack();
            }
            $this->log->error('Error sap invoice interface', ['error' => $e]);
            $status = 'error';
            $msg = 'Unexpected error';
        }

        $response = new SapDocApiResponse();

        $return = new SapDocApiResult();
        $return->setMensaje($msg);
        $return->setEstado($status);

        $response->setReturn($return);

        return $response;
    }

    /**
     * @param $variant
     * @param $country
     * @return \Southbay\Product\Model\SouthbaySapProduct|null
     */
    private function findProduct($variant, $country)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('southbay_catalog_product_sku_variant', $variant);
        $collection->addFieldToFilter('southbay_catalog_product_sap_country_code', $country);
        $collection->load();

        if ($collection->getSize() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    private function invoiceExists(\Southbay\ReturnProduct\Model\SouthbayInvoice $invoice): bool
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SouthbayIvoiceInterfase::ENTITY_INTERNAL_INVOICE_NUMBER, $invoice->getIntInvoiceNum());
        $collection->addFieldToFilter(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_CODE, $invoice->getCustomerCode());
        $collection->addFieldToFilter(SouthbayIvoiceInterfase::ENTITY_INVOICE_DATE, $invoice->getInvoiceDate());
        $collection->addFieldToFilter(SouthbayIvoiceInterfase::ENTITY_COUNTRY_CODE, $invoice->getCountryCode());

        return ($collection->getSize() > 0);
    }

    /**
     * @param $code
     * @param $country
     * @return \Southbay\CustomCustomer\Model\SoldTo|null
     */
    private function findSolTo($code, $country)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->soldToCollectionFactory->create();
        $collection->addFieldToFilter('southbay_sold_to_customer_code', $code);
        $collection->addFieldToFilter('southbay_sold_to_sap_country_code', $country);
        $collection->load();

        if ($collection->getSize() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @param $code
     * @param $sold_to_code
     * @param $country
     * @return \Southbay\CustomCustomer\Model\ShipTo|null
     */
    private function findShipTo($code, $sold_to_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->shipToCollectionFactory->create();
        $collection->addFieldToFilter('southbay_ship_to_code', $code);
        $collection->addFieldToFilter('southbay_ship_to_customer_code', $sold_to_code);
        $collection->load();

        if ($collection->getSize() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

}
