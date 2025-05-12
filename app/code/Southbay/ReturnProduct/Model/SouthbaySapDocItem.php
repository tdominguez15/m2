<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbaySapDocItem as EntityInterface;

class SouthbaySapDocItem extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocItem');
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

    public function getDocId()
    {
        return $this->getData(self::ENTITY_DOC_ID);
    }

    public function setDocId($value)
    {
        $this->setData(self::ENTITY_DOC_ID, $value);
    }

    public function getSku()
    {
        return $this->getData(self::ENTITY_SKU);
    }

    public function setSku($value)
    {
        $this->setData(self::ENTITY_SKU, $value);
    }

    public function getQty()
    {
        return $this->getData(self::ENTITY_QTY);
    }

    public function setQty($value)
    {
        $this->setData(self::ENTITY_QTY, $value);
    }

    public function getPosition()
    {
        return $this->getData(self::ENTITY_POSITION);
    }

    public function setPosition($value)
    {
        $this->setData(self::ENTITY_POSITION, $value);
    }

    public function getNetAmount()
    {
        return $this->getData(self::ENTITY_NET_AMOUNT);
    }

    public function setNetAmount($value)
    {
        $this->setData(self::ENTITY_NET_AMOUNT, $value);
    }
}
