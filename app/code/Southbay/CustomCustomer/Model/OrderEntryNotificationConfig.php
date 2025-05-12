<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\OrderEntryNotificationConfigInterface;

class OrderEntryNotificationConfig extends \Magento\Framework\Model\AbstractModel implements OrderEntryNotificationConfigInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotificationConfig');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function setCountryCode($countryCode)
    {
        $this->setData(self::ENTITY_COUNTRY_CODE, $countryCode);
    }

    public function getCountryCode()
    {
        return $this->getData(self::ENTITY_COUNTRY_CODE);
    }

    public function setFunctionCode($functionCode)
    {
        $this->setData(self::ENTITY_FUNCTION_CODE, $functionCode);
    }

    public function getFunctionCode()
    {
        return $this->getData(self::ENTITY_FUNCTION_CODE);
    }

    public function setTemplateId($templateId)
    {
        $this->setData(self::ENTITY_TEMPLATE_ID, $templateId);
    }

    public function getTemplateId()
    {
        return $this->getData(self::ENTITY_TEMPLATE_ID);
    }

    public function setRetryAfter($retryAfter)
    {
        $this->setData(self::ENTITY_RETRY_AFTER, $retryAfter);
    }

    public function getRetryAfter()
    {
        return $this->getData(self::ENTITY_RETRY_AFTER);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt(string $createdAt)
    {
        $this->setData(self::ENTITY_CREATED_AT, $createdAt);
    }

    public function getUpdatedAt(): string
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt(string $updatedAt)
    {
        $this->setData(self::ENTITY_UPDATED_AT, $updatedAt);
    }
}
