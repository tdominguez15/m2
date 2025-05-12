<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\OrderEntryNotificationInterface;

class OrderEntryNotification extends \Magento\Framework\Model\AbstractModel implements OrderEntryNotificationInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotification');
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

    public function setOrderId($orderId)
    {
        $this->setData(self::ENTITY_ORDER_ID, $orderId);
    }

    public function getOrderId()
    {
        return $this->getData(self::ENTITY_ORDER_ID);
    }

    public function setIncrementId($incrementId)
    {
        $this->setData(self::ENTITY_INCREMENT_ID, $incrementId);
    }

    public function getIncrementId()
    {
        return $this->getData(self::ENTITY_INCREMENT_ID);
    }

    public function setTemplateId($templateId)
    {
        $this->setData(self::ENTITY_TEMPLATE_ID, $templateId);
    }

    public function getTemplateId()
    {
        return $this->getData(self::ENTITY_TEMPLATE_ID);
    }

    public function setEmail($email)
    {
        $this->setData(self::ENTITY_EMAIL, $email);
    }

    public function getEmail()
    {
        return $this->getData(self::ENTITY_EMAIL);
    }

    public function setStatus($status)
    {
        $this->setData(self::ENTITY_STATUS, $status);
    }

    public function getStatus()
    {
        return $this->getData(self::ENTITY_STATUS);
    }

    public function setSendAt($sendAt)
    {
        $this->setData(self::ENTITY_SEND_AT, $sendAt);
    }

    public function getSendAt()
    {
        return $this->getData(self::ENTITY_SEND_AT);
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

    public function setName($name)
    {
        $this->setData(self::ENTITY_NAME, $name);
    }

    public function getName()
    {
        return $this->getData(self::ENTITY_NAME);
    }
}
