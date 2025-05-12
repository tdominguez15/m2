<?php

namespace Southbay\Product\Model;

class ProductExclusion extends \Magento\Framework\Model\AbstractModel implements \Southbay\Product\Api\Data\ProductExclusionInterface
{
    const CACHE_TAG = self::TABLE;

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\ProductExclusion');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getSku()
    {
        return $this->getData(self::ENTITY_SKU);
    }

    public function setSku($sku)
    {
        return $this->setData(self::ENTITY_SKU, $sku);
    }

    public function getStore()
    {
        return $this->getData(self::ENTITY_STORE);
    }

    public function setStore($store)
    {
        return $this->setData(self::ENTITY_STORE, $store);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt($date)
    {
        return $this->setData(self::ENTITY_CREATED_AT, $date);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt($date)
    {
        return $this->setData(self::ENTITY_UPDATED_AT, $date);
    }
}
