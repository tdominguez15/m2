<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Southbay\CustomCustomer\Api\Data\SoldToMapInterface;
use Southbay\CustomCustomer\Api\Data\ShipToMapInterface;
use Southbay\CustomCustomer\Api\Data\ShipToInterface;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoice as SouthbayIvoiceInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem as SouthbayInvoiceItemInterfase;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayInvoiceItemRepository
{
    private $collectionFactory;
    private $invoiceCollectionFactory;
    private $resourceInvoice;

    private $resource;
    private $log;
    private $cache;
    private $returnProductItemRepository;
    private $helper;

    private $filterBuilder;
    private $filterGroupBuilder;
    private $searchCriteriaBuilder;
    private $collectionProcessor;
    private $soldToMapCollectionFactory;
    private $shipToMapCollectionFactory;
    private $shipToCollectionFactory;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem\CollectionFactory $collectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice\CollectionFactory     $invoiceCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem                   $resource,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice                       $resourceInvoice,
                                \Magento\Framework\Model\Context                                                  $context,
                                \Psr\Log\LoggerInterface                                                          $log,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository   $returnProductItemRepository,
                                FilterBuilder                                                                     $filterBuilder,
                                FilterGroupBuilder                                                                $filterGroupBuilder,
                                SearchCriteriaBuilder                                                             $searchCriteriaBuilder,
                                CollectionProcessorInterface                                                      $collectionProcessor,
                                \Southbay\CustomCustomer\Model\ResourceModel\SoldToMap\CollectionFactory          $soldToMapCollectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\ShipToMap\CollectionFactory          $shipToMapCollectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory             $shipToCollectionFactory,
                                \Southbay\ReturnProduct\Helper\Data                                               $helper)
    {
        $this->log = $log;
        $this->cache = $context->getCacheManager();
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->resourceInvoice = $resourceInvoice;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->returnProductItemRepository = $returnProductItemRepository;
        $this->helper = $helper;
        $this->collectionProcessor = $collectionProcessor;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->soldToMapCollectionFactory = $soldToMapCollectionFactory;
        $this->shipToMapCollectionFactory = $shipToMapCollectionFactory;
        $this->shipToCollectionFactory = $shipToCollectionFactory;
    }

    /**
     * @return mixed
     */
    public function searchBySku($sku, $return_type, $customer_code, $country_code, $page = 1, $total = 20)
    {
        $codes = [];

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->shipToCollectionFactory->create();
        $collection->addFieldToFilter(ShipToInterface::SOUTHBAY_SHIP_TO_CUSTOMER_CODE, $customer_code);

        if ($collection->count() == 0) {
            return ['list' => [], 'total_pages' => 0];
        }

        $ship_to_list = $collection->getItems();

        /**
         * @var ShipToInterface $ship_to
         */
        foreach ($ship_to_list as $ship_to) {
            $codes[] = [
                'ship_to' => $ship_to->getCode(),
                'sold_to' => $customer_code
            ];

            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->shipToMapCollectionFactory->create();
            $collection->addFieldToFilter(ShipToMapInterface::SHIP_TO_CODE, $ship_to->getCode());
            $collection->addFieldToFilter(ShipToMapInterface::SOLD_TO_CODE, $customer_code);

            if ($collection->count() > 0) {
                $items = $collection->getItems();

                /**
                 * @var ShipToMapInterface $map
                 */
                foreach ($items as $map) {
                    $codes[] = [
                        'ship_to' => $map->getShipToOldCode(),
                        'sold_to' => $map->getSoldToOldCode()
                    ];
                }
            }
        }

        return $this->_searchBySku($sku, $return_type, $codes, $country_code, $page, $total);
    }

    /**
     * @param $sku
     * @return mixed
     */
    private function _searchBySku($sku, $return_type, $codes, $country_code, $page = 1, $total = 20)
    {
        $collection = $this->collectionFactory->create();
        $collection->join(['invoice' => SouthbayIvoiceInterfase::TABLE],
            "invoice.southbay_invoice_id = main_table.southbay_invoice_id"
        );

        $conditions = [];

        foreach ($codes as $code) {
            $field1 = 'invoice.southbay_customer_ship_to_code';
            $field2 = 'invoice.southbay_customer_code';
            $value1 = $code['ship_to'];
            $value2 = $code['sold_to'];

            $conditions[] = "($field1 = '$value1' AND $field2 = '$value2')";
        }

        $collection
            ->getSelect()
            ->joinLeft(['balance' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::TABLE],
                "balance.southbay_return_balance_invoice_item_id = main_table.southbay_invoice_item_id"
            )
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                "main_table.*",
                'invoice.southbay_invoice_date',
                'invoice.southbay_int_invoice_num',
                'invoice.southbay_invoice_ref',
                'invoice.southbay_customer_ship_to_code',
                'invoice.southbay_customer_ship_to_name',
                'balance.southbay_return_balance_total_available'
            ]);

        $collection->getSelect()->where("(" . implode(' OR ', $conditions) . ")");

        $config = $this->helper->getConfig($return_type, $country_code);

        if (!is_null($config)) {
            $max_years = $config->getMaxYearHistory();

            $date = new \DateTime();
            $date->setTime(0, 0, 0, 0);
            $date->modify("-$max_years year");

            $collection->addFieldToFilter('invoice.southbay_invoice_date', ['gteq' => $date->format('Y-m-d')]);
            $collection->setOrder('invoice.southbay_invoice_date', $config->getOrder());
        }

        $filterSku = $this->filterBuilder
            ->setField(\Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem::ENTITY_SKU)
            ->setValue("%$sku%")
            ->setConditionType('like')
            ->create();

        $filterSkuGeneric = $this->filterBuilder
            ->setField(\Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem::ENTITY_SKU_GENERIC)
            ->setValue("%$sku%")
            ->setConditionType('like')
            ->create();

        $filterSkuVariant = $this->filterBuilder
            ->setField(\Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem::ENTITY_SKU_VARIANT)
            ->setValue("%$sku%")
            ->setConditionType('like')
            ->create();


        $filterGroup = $this->filterGroupBuilder->setFilters([
            $filterSku, $filterSkuVariant, $filterSkuGeneric
        ])->create();

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setFilterGroups([$filterGroup]);

        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->setPageSize($total);
        $collection->setCurPage($page);
        $collection->load(false, true);

        $total_pages = 0;
        $data = $collection->getData();
        $result = [];

        if (!is_null($data)) {
            $total_pages = $collection->getLastPageNumber();

            foreach ($data as $item) {
                if (is_null($item['southbay_return_balance_total_available'])) {
                    $total_available = intval($item['southbay_invoice_item_qty']);
                } else {
                    $total_available = intval($item['southbay_return_balance_total_available']);
                }

                if ($total_available > 0) {
                    $result[] = ['item' => $item, 'total_available' => $total_available];
                }
            }
        }

        return ['list' => $result, 'total_pages' => $total_pages];
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem|null
     */
    public function findById($id)
    {
        $identifier = SouthbayInvoiceItemInterfase::CACHE_TAG . '_' . $id;

        $item = $this->cache->load($identifier);

        if ($item) {
            return unserialize($item);
        } else {
            $collection = $this->findByAttributeName(SouthbayInvoiceItemInterfase::ENTITY_ID, $id);
            $collection->setPageSize(1);
            $collection->setCurPage(1);

            if ($collection->getSize() > 0) {
                $item = $collection->getFirstItem();

                $this->cache->save(serialize($item), $identifier, [SouthbayInvoiceItemInterfase::CACHE_TAG]);

                return $item;
            } else {
                return null;
            }
        }
    }

    /**
     * @param $name
     * @param $value
     * @return AbstractCollection
     */
    private function findByAttributeName($name, $value)
    {
        $collection = $this->collectionFactory->create();
        return $collection->addFieldToFilter($name, ['eq' => $value]);
    }

    /**
     * @param $id
     * @param $value
     * @return AbstractCollection
     */
    private function findByAttributeNameInvoice($name, $value)
    {
        $collection = $this->invoiceCollectionFactory->create();
        return $collection->addFieldToFilter($name, ['eq' => $value]);
    }

    public function save($model)
    {
        return $this->resource->save($model);
    }
}
