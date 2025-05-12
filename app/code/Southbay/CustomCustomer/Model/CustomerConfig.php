<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\CustomerConfigInterface;

class CustomerConfig extends \Magento\Framework\Model\AbstractModel implements CustomerConfigInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\CustomCustomer\Model\ResourceModel\CustomerConfig');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function setCreatedAt($value)
    {
        $this->setData(self::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(self::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }


    public function getMagentoCustomerEmail()
    {
        return $this->getData(self::ENTITY_MAGENTO_CUSTOMER_EMAIL);
    }

    public function setMagentoCustomerEmail($value)
    {
        $this->setData(self::ENTITY_MAGENTO_CUSTOMER_EMAIL, $value);
    }

    public function getSoldToIds()
    {
        return $this->getData(self::ENTITY_SOLD_TO_IDS);
    }

    public function setSoldToIds($value)
    {
        $this->setData(self::ENTITY_SOLD_TO_IDS, $value);
    }

    public function getCountriesCodes()
    {
        return $this->getData(self::ENTITY_COUNTRIES_CODES);
    }

    public function setCountriesCodes($value)
    {
        $this->setData(self::ENTITY_COUNTRIES_CODES, $value);
    }

    public function getFunctionsCodes()
    {
        return $this->getData(self::ENTITY_FUNCTIONS_CODES);
    }

    public function setFunctionsCodes($value)
    {
        $this->setData(self::ENTITY_FUNCTIONS_CODES, $value);
    }
}
