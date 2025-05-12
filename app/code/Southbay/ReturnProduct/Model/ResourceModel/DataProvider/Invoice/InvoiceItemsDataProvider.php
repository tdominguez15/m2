<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\Invoice;

use Magento\Backend\Model\Auth\Session;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayInvoiceItemCollectionFactory;

class InvoiceItemsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    protected $log;
    private $context;
    protected $collectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Backend\App\Action\Context $context,
        SouthbayInvoiceItemCollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $log,
        array $meta = [],
        array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->log = $log;
        $this->context = $context;
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->collectionFactory->create();

        }
        return $this->collection;
    }


    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [
            'items' => [],
            'totalRecords' => 0
        ];

        $invoice_id = $this->context->getSession()->getInvoiceId();

        if (empty($invoice_id)) {
            return $this->loadedData;
        }

        $collection = $this->getCollection();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem::ENTITY_INVOICE_ID, ['eq' => $invoice_id]);
        $collection->load();

        $items = $collection->getItems();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem $item
         */
        foreach ($items as $item) {
            $this->loadedData['items'][] = $item->getData();
        }

        $this->loadedData['totalRecords'] = $collection->getSize();

        return $this->loadedData;
    }
}
