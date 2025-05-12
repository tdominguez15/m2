<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayNotificationHistoryRtv as EntityInterface;

class SouthbayNotificationHistoryRtv extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayNotificationHistoryRtv');
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

    public function getFrom()
    {
        return $this->getData(self::ENTITY_FROM);
    }

    public function setFrom($value)
    {
        $this->setData(self::ENTITY_FROM, $value);
    }

    public function getTo()
    {
        return $this->getData(self::ENTITY_TO);
    }

    public function setTo($value)
    {
        $this->setData(self::ENTITY_TO, $value);
    }

    public function getType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    public function setReturnId($value)
    {
        $this->setData(self::ENTITY_RETURN_ID, $value);
    }

    public function getReturnId()
    {
        return $this->getData(self::ENTITY_RETURN_ID);
    }

    public function setType($value)
    {
        $this->setData(self::ENTITY_TYPE, $value);
    }

    public function getSubject()
    {
        return $this->getData(self::ENTITY_SUBJECT);
    }

    public function setSubject($value)
    {
        $this->setData(self::ENTITY_SUBJECT, $value);
    }

    public function getContent()
    {
        return $this->getData(self::ENTITY_CONTENT);
    }

    public function setContent($value)
    {
        $this->setData(self::ENTITY_CONTENT, $value);
    }

    public function getCustomerCode()
    {
        return $this->getData(self::ENTITY_CUSTOMER_CODE);
    }

    public function setCustomerCode($value)
    {
        $this->setData(self::ENTITY_CUSTOMER_CODE, $value);
    }
}
