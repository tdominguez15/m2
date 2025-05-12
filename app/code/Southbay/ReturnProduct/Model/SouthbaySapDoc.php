<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbaySapDoc as EntityInterface;

class SouthbaySapDoc extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDoc');
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

    public function getSapInterfaceId()
    {
        return $this->getData(self::ENTITY_SAP_INTERFACE_ID);
    }

    public function setSapInterfaceId($value)
    {
        $this->setData(self::ENTITY_SAP_INTERFACE_ID, $value);
    }

    public function getTypeDoc()
    {
        return $this->getData(self::ENTITY_TYPE_DOC);
    }

    public function setTypeDoc($value)
    {
        $this->setData(self::ENTITY_TYPE_DOC, $value);
    }

    public function getDocInternalNumber()
    {
        return $this->getData(self::ENTITY_DOC_INTERNAL_NUMBER);
    }

    public function setDocInternalNumber($value)
    {
        $this->setData(self::ENTITY_DOC_INTERNAL_NUMBER, $value);
    }

    public function getDocLegalNumber()
    {
        return $this->getData(self::ENTITY_DOC_LEGAL_NUMBER);
    }

    public function setDocLegalNumber($value)
    {
        $this->setData(self::ENTITY_DOC_LEGAL_NUMBER, $value);
    }

    public function getTotalNetAmount()
    {
        return $this->getData(self::ENTITY_TOTAL_NET_AMOUNT);
    }

    public function setTotalNetAmount($value)
    {
        $this->setData(self::ENTITY_TOTAL_NET_AMOUNT, $value);
    }

    public function getTotalAmount()
    {
        return $this->getData(self::ENTITY_TOTAL_AMOUNT);
    }

    public function setTotalAmount($value)
    {
        $this->setData(self::ENTITY_TOTAL_AMOUNT, $value);
    }
}
