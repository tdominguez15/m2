<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider;

abstract class DataProviderBase extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $returnTypeOptionsProvider;
    protected $clientOptionsProvider;
    protected $loadedData;
    protected $collection_factory;
    protected $context;

    protected $returnProductRepository;
    protected $log;

    public function __construct(\Magento\Backend\App\Action\Context                                                  $context,
                                \Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnTypeOptionsProvider          $returnTypeOptionsProvider,
                                \Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnProductClientOptionsProvider $clientOptionsProvider,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository          $returnProductRepository,
                                                                                                                     $name,
                                                                                                                     $primaryFieldName,
                                                                                                                     $requestFieldName,
                                \Psr\Log\LoggerInterface                                                             $log,
                                array                                                                                $meta = [],
                                array                                                                                $data = [])
    {
        $this->context = $context;
        $this->returnTypeOptionsProvider = $returnTypeOptionsProvider;
        $this->clientOptionsProvider = $clientOptionsProvider;
        $this->returnProductRepository = $returnProductRepository;
        $this->log = $log;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data);
    }

    public static function setupSelect($select)
    {
        $select->setPart(\Magento\Framework\DB\Select::COLUMNS, ['return_product', 'southbay_return_type', 'southbay_return_type']);
        $select->setPart(\Magento\Framework\DB\Select::COLUMNS, ['return_product', 'southbay_return_customer_code', 'southbay_return_customer_code']);
    }

    public static function setupJoin($collection)
    {
        $collection->join(['return_product' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TABLE],
            "return_product.southbay_return_id = main_table.southbay_return_id"
        );
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->initCollection();
        }
        return $this->collection;
    }

    protected function initCollection()
    {
        $collection = $this->collection_factory->create();
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                "main_table.*",
                'return_product.southbay_return_type AS southbay_return_type',
                'return_product.southbay_return_customer_code AS southbay_return_customer_code'
            ]);

        self::setupJoin($collection);

        return $collection;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $id = $this->context->getRequest()->getParam('id');

        if ($id) {
            $item = $this->collection->getItemById($id);
            if ($item) {
                $this->loadedData[$item->getId()]['fields'] = $item->getData();
            }
        }

        if (empty($this->loadedData)) {
            return $this->loadedData;
        }

        $customers = $this->optionsToMap($this->clientOptionsProvider->toOptionArray());
        $types = $this->optionsToMap($this->returnTypeOptionsProvider->toOptionArray());

        $data = $this->loadedData;

        foreach ($data as $key => $_item) {
            $type_label = '';
            $customer_name = '';

            if (isset($types[$_item['fields']['southbay_return_type']])) {
                $type_label = $types[$_item['fields']['southbay_return_type']];
            }

            if (isset($customers[$_item['fields']['southbay_return_customer_code']])) {
                $customer_name = $customers[$_item['fields']['southbay_return_customer_code']];
            }

            $_item['fields']['southbay_return_product_type'] = $type_label;
            $_item['fields']['southbay_return_product_customer'] = $customer_name;

            $fields = $_item['fields'];

            $_item['fields'] = [
                'detail' => $fields
            ];

            $data[$key] = $this->getItem($_item);
        }

        $this->loadedData = $data;

        return $this->loadedData;
    }

    protected function getItem($item)
    {
        return $item;
    }

    public function optionsToMap($options)
    {
        $result = [];

        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }
}
