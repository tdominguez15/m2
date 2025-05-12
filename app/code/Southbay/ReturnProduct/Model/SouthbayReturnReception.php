<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnReception as SouthbayReturnReceptionInterfase;

class SouthbayReturnReception extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnReceptionInterfase
{
    protected $_cacheTag = SouthbayReturnReceptionInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayReturnReceptionInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnReception');
    }

    public function getIdentities(): array
    {
        return [SouthbayReturnReceptionInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function setCountryCode($value)
    {
        $this->setData(SouthbayReturnReceptionInterfase::ENTITY_COUNTRY_CODE, $value);
    }

    public function getCountryCode()
    {
        return $this->getData(SouthbayReturnReceptionInterfase::ENTITY_COUNTRY_CODE);
    }


    public function setCreatedAt($value)
    {
        $this->setData(SouthbayReturnReceptionInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayReturnReceptionInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayReturnReceptionInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayReturnReceptionInterfase::ENTITY_UPDATED_AT);
    }

    public function getReturnId()
    {
        return $this->getData(SouthbayReturnReceptionInterfase::ENTITY_RETURN_ID);
    }

    public function setReturnId($value)
    {
        $this->setData(SouthbayReturnReceptionInterfase::ENTITY_RETURN_ID, $value);
    }

    public function getUserCode()
    {
        return $this->getData(SouthbayReturnReceptionInterfase::ENTITY_USER_CODE);
    }

    public function setUserCode($value)
    {
        $this->setData(SouthbayReturnReceptionInterfase::ENTITY_USER_CODE, $value);
    }

    public function getUserName()
    {
        return $this->getData(SouthbayReturnReceptionInterfase::ENTITY_USER_NAME);
    }

    public function setUserName($value)
    {
        $this->setData(SouthbayReturnReceptionInterfase::ENTITY_USER_NAME, $value);
    }

    public function getTotalPackages()
    {
        return $this->getData(SouthbayReturnReceptionInterfase::ENTITY_TOTAL_PACKAGES);
    }

    public function setTotalPackages($value)
    {
        $this->setData(SouthbayReturnReceptionInterfase::ENTITY_TOTAL_PACKAGES, $value);
    }
}
