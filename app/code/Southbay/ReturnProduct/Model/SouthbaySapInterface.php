<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbaySapInterface as EntityInterface;

class SouthbaySapInterface extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterface');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
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

    public function getRef()
    {
        return $this->getData(self::ENTITY_REF);
    }

    public function setRef($value)
    {
        $this->setData(self::ENTITY_REF, $value);
    }

    public function getRequest()
    {
        return $this->getData(self::ENTITY_REQUEST);
    }

    public function setRequest($value)
    {
        $this->setData(self::ENTITY_REQUEST, $value);
    }

    public function getResponse()
    {
        return $this->getData(self::ENTITY_RESPONSE);
    }

    public function setResponse($value)
    {
        $this->setData(self::ENTITY_RESPONSE, $value);
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

    public function setEnd($value)
    {
        $this->setData(self::ENTITY_END, $value);
    }

    public function getEnd()
    {
        return $this->getData(self::ENTITY_END);
    }

    public function setUrl($value)
    {
        $this->setData(self::ENTITY_URL, $value);
    }

    public function getUrl()
    {
        return $this->getData(self::ENTITY_URL);
    }
}
