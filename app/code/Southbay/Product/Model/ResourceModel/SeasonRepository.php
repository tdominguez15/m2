<?php

namespace Southbay\Product\Model\ResourceModel;

use Southbay\Product\Api\Data\SeasonInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SeasonRepository
{
    private $collectionFactory;
    private $seasonResource;
    private $log;
    private $timezone;
    private $storeManager;

    private $storeConfigManager;

    public function __construct(
        \Psr\Log\LoggerInterface                                       $log,
        \Southbay\Product\Model\ResourceModel\Season\CollectionFactory $collectionFactory,
        \Magento\Store\Api\StoreConfigManagerInterface                 $storeConfigManager,
        StoreManagerInterface                                          $storeManager,
        TimezoneInterface                                              $timezone,
        \Southbay\Product\Model\ResourceModel\Season                   $seasonResource)
    {
        $this->log = $log;
        $this->collectionFactory = $collectionFactory;
        $this->seasonResource = $seasonResource;
        $this->storeManager = $storeManager;
        $this->storeConfigManager = $storeConfigManager;
        $this->timezone = $timezone;
    }

    /**
     * @param $id
     * @return \Southbay\Product\Model\Season|null
     */
    public function findById($id)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        return $collection->getItemById($id);
    }

    /**
     * @return \Southbay\Product\Model\Season|null
     */
    public function findCurrent($store_id)
    {
        $store = $this->storeManager->getStore($store_id);
        // $config = $this->getStoreConfig($store_id);
        // return $this->_findCurrent($store, $config);
        return $this->_findCurrent($store);
    }

    /**
     * @return \Southbay\Product\Model\Season|null
     */
    private function _findCurrent(\Magento\Store\Api\Data\StoreInterface $store)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SeasonInterface::ENTITY_SEASON_STORE_ID, $store->getId());
        $collection->addFieldToFilter(SeasonInterface::ENTITY_SEASON_ACTIVE, ['eq' => true]);
        $collection->load();

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /*
    private function _findCurrent(\Magento\Store\Api\Data\StoreInterface $store, \Magento\Store\Api\Data\StoreConfigInterface $config)
    {
        $current_date = $this->timezone->date()->setTimezone(new \DateTimeZone($config->getTimezone()));
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SeasonInterface::ENTITY_SEASON_START_AT, ['gte' => $current_date->format('Y-m-d')]);
        $collection->addFieldToFilter(SeasonInterface::ENTITY_SEASON_END_AT, ['lte' => $current_date->format('Y-m-d')]);
        $collection->addFieldToFilter(SeasonInterface::ENTITY_SEASON_STORE_ID, $store->getId());
        $collection->load();

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }

        return null;
    }
    */

    public function getMonthForDeliveryFromCurrent(\Magento\Store\Api\Data\StoreInterface $store)
    {
        $config = $this->getStoreConfig($store->getCode());
        $locate = $config->getLocale();

        /**
         * @var \Southbay\Product\Model\Season $model
         */
        $model = $this->_findCurrent($store, $config);
        // $model = $this->_findCurrent($store, $config);

        if (is_null($model)) {
            return [];
        }

        $months = [];

        if (!empty($model->getMonthDeliveryDate1())) {
            $months[] = [
                'code' => SeasonInterface::ENTITY_SEASON_MONTH_DELIVERY_DATE_1,
                'label' => $this->_getMonthLabel($model->getMonthDeliveryDate1(), $locate),
                'date' => $model->getMonthDeliveryDate1()
            ];
        }

        if (!empty($model->getMonthDeliveryDate2())) {
            $months[] = [
                'code' => SeasonInterface::ENTITY_SEASON_MONTH_DELIVERY_DATE_2,
                'label' => $this->_getMonthLabel($model->getMonthDeliveryDate2(), $locate),
                'date' => $model->getMonthDeliveryDate2()
            ];
        }

        if (!empty($model->getMonthDeliveryDate3())) {
            $months[] = [
                'code' => SeasonInterface::ENTITY_SEASON_MONTH_DELIVERY_DATE_3,
                'label' => $this->_getMonthLabel($model->getMonthDeliveryDate3(), $locate),
                'date' => $model->getMonthDeliveryDate3()
            ];
        }

        return $months;
    }

    private function _getMonthLabel($str_date, $locate)
    {
        $date = new \DateTime($str_date);

        $formatter = $this->getMonthFormatter($locate);
        $result = $formatter->format($date);

        $result = str_replace(' ', '', $result);
        return ucwords(str_replace('.', '', $result));
    }

    public function getMonthFormatter($locate)
    {
        $formatter = new \IntlDateFormatter($locate, \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        $formatter->setPattern('MMM/yy');

        return $formatter;
    }

    public function parseMonthLabelToDate($label, \Magento\Store\Api\Data\StoreInterface $store)
    {
        $label = strtolower($label);
        $config = $this->getStoreConfig($store->getCode());
        $locate = $config->getLocale();
        $formatter = $this->getMonthFormatter($locate);
        return $formatter->parse($label);
    }

    private function findFirstByAttributeName($name, $value)
    {
        $collection = $this->findByAttributeName($name, $value);
        $result = $collection->getFirstItem();

        if ($collection->getSize() == 0) {
            return null;
        }

        return $result;
    }

    private function findByAttributeName($name, $value, $page = array())
    {
        $collection = $this->collectionFactory->create();
        return $collection->addFieldToFilter($name, ['eq' => $value]);
    }

    public function save($model)
    {
        $model->setSeasonEnabled(false);
        return $model;
    }

    public function find($id)
    {
        return $this->findFirstByAttributeName(SeasonInterface::ENTITY_ID, $id);
    }

    private function _save($model)
    {
        $this->seasonResource->save($model);
        return $model;
    }

    /**
     * @param $store_code
     * @return \Magento\Store\Api\Data\StoreConfigInterface
     */
    private function getStoreConfig($store_code)
    {
        $config = $this->storeConfigManager->getStoreConfigs([$store_code]);
        return $config[0];
    }
}
