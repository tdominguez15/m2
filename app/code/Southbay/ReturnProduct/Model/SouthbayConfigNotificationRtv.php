<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv as EntityInterface;

class SouthbayConfigNotificationRtv extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtv');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getCountryCode()
    {
        return $this->getData(self::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(self::ENTITY_COUNTRY_CODE, $value);
    }

    public function getType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    public function setType($value)
    {
        $this->setData(self::ENTITY_TYPE, $value);
    }

    public function getTemplateCode()
    {
        return $this->getData(self::ENTITY_TEMPLATE_CODE);
    }

    public function setTemplateCode($value)
    {
        $this->setData(self::ENTITY_TEMPLATE_CODE, $value);
    }

    public function getStatus()
    {
        return $this->getData(self::ENTITY_STATUS);
    }

    public function setStatus($value)
    {
        $this->setData(self::ENTITY_STATUS, $value);
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
}
