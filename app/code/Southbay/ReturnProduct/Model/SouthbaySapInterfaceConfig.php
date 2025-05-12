<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig as EntityInterface;

class SouthbaySapInterfaceConfig extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterfaceConfig');
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

    public function setUrl($value)
    {
        $this->setData(self::ENTITY_URL, $value);
    }

    public function getUrl()
    {
        return $this->getData(self::ENTITY_URL);
    }

    public function getType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    public function setType($value)
    {
        $this->setData(self::ENTITY_TYPE, $value);
    }

    public function getUsername()
    {
        return $this->getData(self::ENTITY_USERNAME);
    }

    public function setUsername($value)
    {
        $this->setData(self::ENTITY_USERNAME, $value);
    }

    public function getPassword()
    {
        return $this->getData(self::ENTITY_PASSWORD);
    }

    public function setPassword($value)
    {
        $this->setData(self::ENTITY_PASSWORD, $value);
    }
}
