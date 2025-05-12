<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\Invoice;

use Magento\Backend\Model\Auth\Session;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayInvoiceCollectionFactory as CollectionFactory;

class InvoiceDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    private $collectionFactory;
    private $context;
    private $helper;

    public function __construct(CollectionFactory                   $collectionFactory,
                                \Southbay\ReturnProduct\Helper\Data $helper,
                                \Magento\Backend\App\Action\Context $context,
                                                                    $name,
                                                                    $primaryFieldName,
                                                                    $requestFieldName,
                                array                               $meta = [],
                                array                               $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->context = $context;
        $this->helper = $helper;
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->collectionFactory->create();
        }
        return $this->collection;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $map = $this->helper->getSapCountriesMap();
        $this->loadedData = [];
        $id = $this->context->getRequest()->getParam('id');

        if ($id) {
            $item = $this->collection->getItemById($id);
            if ($item) {
                $data = $item->getData();

                if (isset($map[$data['southbay_invoice_country_code']])) {
                    $data['southbay_invoice_country_code'] = $map[$data['southbay_invoice_country_code']];
                } else {
                    $data['southbay_invoice_country_code'] = '';
                }

                $this->loadedData[$item->getId()]['fields'] = $data;
            }
        }

        return $this->loadedData;
    }
}
