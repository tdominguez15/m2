<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory;

class ConfigStoreRepository implements ConfigStoreRepositoryInterface
{
    protected $configFactory;
    protected $configCollectionFactory;

    public function __construct(
        ConfigStoreFactory $configFactory,
        CollectionFactory  $configCollectionFactory
    )
    {
        $this->configFactory = $configFactory;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @param $id
     * @return ConfigStoreInterface|null
     */
    public function findByStoreId($id)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter(ConfigStoreInterface::SOUTHBAY_STORE_CODE, $id);

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    public function getById($id)
    {
        $config = $this->configFactory->create();
        $config->load($id);
        return $config;
    }

    public function createOrUpdate($data)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter(ConfigStoreInterface::SOUTHBAY_STORE_CODE, $data['store_code']);

        /**
         * @var ConfigStoreInterface $config
         */
        $config = $collection->getFirstItem();
        $config->setSouthbayStoreCode($data['store_code']);
        $config->setSouthbayFunctionCode($data['function_code']);
        $config->setSouthbayCountryCode($data['country_code']);

        $config->save();
        return $config;
    }

    public function save(ConfigStoreInterface $config)
    {
        $config->save();
        return $config;
    }

    public function delete(ConfigStoreInterface $config)
    {
        $config->delete();
    }

    /**
     * @return ConfigStoreInterface[]
     */
    public function findByFunctionCode($code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter(ConfigStoreInterface::SOUTHBAY_FUNCTION_CODE, $code);
        $collection->load();

        return $collection->getItems();
    }

    /**
     * @return ConfigStoreInterface|null
     */
    public function findStoreByFunctionCodeAndCountry($code, $country_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter(ConfigStoreInterface::SOUTHBAY_FUNCTION_CODE, $code);
        $collection->addFieldToFilter(ConfigStoreInterface::SOUTHBAY_COUNTRY_CODE, $country_code);

        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isAtOnce(int $storeId): bool
    {
        $configStore = $this->findByStoreId($storeId);
        return $this->isAtOnceByConfig($configStore);
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isAtOnceByConfig($configStore): bool
    {
        if ($configStore && $configStore->getData(ConfigStoreRepositoryInterface::SOUTHBAY_FUNCTION_CODE) === ConfigStoreRepositoryInterface::SOUTHBAY_AT_ONCE) {
            return (bool)$configStore->getData(ConfigStoreRepositoryInterface::SOUTHBAY_FUNCTION_CODE);
        }

        return false;
    }
}
