<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv as EntityInterface;

class SouthbayRolConfigRtv extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayRolConfigRtv');
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

    public function getTypeRol()
    {
        return $this->getData(self::ENTITY_TYPE_ROL);
    }

    public function setTypeRol($value)
    {
        $this->setData(self::ENTITY_TYPE_ROL, $value);
    }

    public function getType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    public function setType($value)
    {
        $this->setData(self::ENTITY_TYPE, $value);
    }

    public function getRolCode()
    {
        return $this->getData(self::ENTITY_ROL_CODE);
    }

    public function setRolCode($value)
    {
        $this->setData(self::ENTITY_ROL_CODE, $value);
    }

    public function getApprovalUseAmountLimit()
    {
        return $this->getData(self::ENTITY_APPROVAL_USE_AMOUNT_LIMIT);
    }

    public function setApprovalUseAmountLimit($value)
    {
        $this->setData(self::ENTITY_APPROVAL_USE_AMOUNT_LIMIT, $value);
    }

    public function getApprovalAmountLimit()
    {
        return $this->getData(self::ENTITY_APPROVAL_AMOUNT_LIMIT);
    }

    public function setApprovalAmountLimit($value)
    {
        $this->setData(self::ENTITY_APPROVAL_AMOUNT_LIMIT, $value);
    }

    public function setRequireAllMembers($value)
    {
        $this->setData(self::ENTITY_REQUIRE_ALL_MEMBERS, $value);
    }

    public function getRequireAllMembers()
    {
        return $this->getData(self::ENTITY_REQUIRE_ALL_MEMBERS);
    }
}
