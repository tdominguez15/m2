<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\MapCountryInterface;
use Southbay\CustomCustomer\Api\MapCountryRepositoryInterface;
use Southbay\CustomCustomer\Model\ResourceModel\MapCountry\CollectionFactory;

class MapCountryRepository implements MapCountryRepositoryInterface
{
    protected $mapCountryFactory;
    protected $mapCountryCollectionFactory;
    private $log;

    public function __construct(
        MapCountryFactory        $mapCountryFactory,
        CollectionFactory        $mapCountryCollectionFactory,
        \Psr\Log\LoggerInterface $log
    )
    {
        $this->mapCountryFactory = $mapCountryFactory;
        $this->mapCountryCollectionFactory = $mapCountryCollectionFactory;
        $this->log = $log;
    }

    public function getById($id)
    {
        $mapCountry = $this->mapCountryFactory->create();
        $mapCountry->load($id);
        return $mapCountry;
    }

    public function save(MapCountryInterface $mapCountry)
    {
        $mapCountry->save();
        return $mapCountry;
    }

    public function delete(MapCountryInterface $mapCountry)
    {
        $mapCountry->delete();
    }

    /**
     * @return \Southbay\CustomCustomer\Api\Data\MapCountryInterface[]
     */
    public function getAll()
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\MapCountry\Collection $collection
         */
        $collection = $this->mapCountryCollectionFactory->create();
        $collection->load();

        return $collection->getItems();
    }

    public function toMap()
    {
        $items = $this->getAll();
        $result = [];

        /**
         * @var \Southbay\CustomCustomer\Api\Data\MapCountryInterface $item
         */
        foreach ($items as $item) {
            $result[$item->getCountryCode()] = $item->getSapCountryCode();
            if (!empty($item->getSapCountryCodeFrontera())) {
                $result[$item->getCountryCode() . '_FRONTERA'] = $item->getSapCountryCodeFrontera();
            }
        }

        return $result;
    }

    /**
     * @param $code
     * @return MapCountryInterface|null
     */
    public function findBySapStockCountryCode($code)
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\MapCountry\Collection $collection
         */
        $collection = $this->mapCountryCollectionFactory->create();
        $collection->addFieldToFilter('southbay_map_sap_source_code', $code);

        if ($collection->count() === 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @param $code
     * @return MapCountryInterface|null
     */
    public function findBySapCountryCode($code)
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\MapCountry\Collection $collection
         */
        $collection = $this->mapCountryCollectionFactory->create();
        $collection->addFieldToFilter('southbay_map_sap_country_code', $code);

        if ($collection->count() === 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @param $countryCode
     * @return MapCountryInterface|null
     */
    public function findByCountryCode($countryCode)
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\MapCountry\Collection $collection
         */
        $collection = $this->mapCountryCollectionFactory->create();
        $collection->addFieldToFilter('southbay_map_country_code', $countryCode);

        if ($collection->count() === 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    public function toSapMap()
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\MapCountry\Collection $collection
         */
        $collection = $this->mapCountryCollectionFactory->create();
        $collection->load();
        $items = $collection->getItems();

        $result = [];

        /**
         * @var \Southbay\CustomCustomer\Api\Data\MapCountryInterface $item
         */
        foreach ($items as $item) {
            $result[$item->getSapCountryCode()] = $item->getCountryCode();
            if (!empty($item->getSapCountryCodeFrontera())) {
                $result[$item->getSapCountryCodeFrontera()] = $item->getCountryCode() . '-FRONTERA';
            }
        }

        return $result;
    }
}
