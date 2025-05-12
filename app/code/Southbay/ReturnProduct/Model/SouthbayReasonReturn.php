<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn as SouthbayReasonReturnInterfase;

class SouthbayReasonReturn extends \Magento\Framework\Model\AbstractModel implements SouthbayReasonReturnInterfase
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReasonReturn');
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

    public function getCode()
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function setCode($value)
    {
        $this->setData(self::ENTITY_CODE, $value);
    }

    public function getName()
    {
        return $this->getData(self::ENTITY_NAME);
    }

    public function setName($value)
    {
        $this->setData(self::ENTITY_NAME, $value);
    }
}
