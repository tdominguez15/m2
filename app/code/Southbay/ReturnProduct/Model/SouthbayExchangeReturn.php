<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn as EntityInterface;

class SouthbayExchangeReturn extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayExchangeReturn');
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

    public function getCountryCode()
    {
        return $this->getData(self::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(self::ENTITY_COUNTRY_CODE, $value);
    }

    public function getExchange()
    {
        return $this->getData(self::ENTITY_EXCHANGE);
    }

    public function setExchange($value)
    {
        $this->setData(self::ENTITY_EXCHANGE, $value);
    }

    public function getUserCode()
    {
        return $this->getData(self::ENTITY_USER_CODE);
    }

    public function setUserCode($value)
    {
        $this->setData(self::ENTITY_USER_CODE, $value);
    }

    public function getUserName()
    {
        return $this->getData(self::ENTITY_USER_NAME);
    }

    public function setUserName($value)
    {
        $this->setData(self::ENTITY_USER_NAME, $value);
    }
}
