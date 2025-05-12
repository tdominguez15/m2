<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbaySapCheckStatus as EntityInterface;

class SouthbaySapCheckStatus extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapCheckStatus');
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

    public function getResponse()
    {
        return $this->getData(self::ENTITY_RESPONSE);
    }

    public function setResponse($value)
    {
        $this->setData(self::ENTITY_RESPONSE, $value);
    }

    public function setCheckSum($value)
    {
        $this->setData(self::ENTITY_CHECK_SUM, $value);
    }

    public function getCheckSum()
    {
        return $this->getData(self::ENTITY_CHECK_SUM);
    }

    public function setSapInterfaceId($value)
    {
        $this->setData(self::ENTITY_SAP_INTERFACE_ID, $value);
    }

    public function getSapInterfaceId()
    {
        return $this->getData(self::ENTITY_SAP_INTERFACE_ID);
    }
}
