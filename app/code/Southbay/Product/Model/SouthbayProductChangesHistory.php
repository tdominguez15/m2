<?php

namespace Southbay\Product\Model;

use \Southbay\Product\Api\Data\SouthbayProductChangesHistory as ModelInterface;

class SouthbayProductChangesHistory extends \Magento\Framework\Model\AbstractModel implements ModelInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbayProductChangesHistory');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt($date)
    {
        $this->setData(self::ENTITY_CREATED_AT, $date);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt($date)
    {
        $this->setData(self::ENTITY_UPDATED_AT, $date);
    }

    public function getProductId()
    {
        return $this->getData(self::ENTITY_PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        $this->setData(self::ENTITY_PRODUCT_ID, $productId);
    }

    public function getStoreId()
    {
        return $this->getData(self::ENTITY_STORE_ID);
    }

    public function setStoreId($storeId)
    {
        $this->setData(self::ENTITY_STORE_ID, $storeId);
    }

    public function getHash()
    {
        return $this->getData(self::ENTITY_HASH);
    }

    public function setHash($hash)
    {
        $this->setData(self::ENTITY_HASH, $hash);
    }

    public function getJsonData()
    {
        return $this->getData(self::ENTITY_JSON_DATA);
    }

    public function setJsonData($data)
    {
        $this->setData(self::ENTITY_JSON_DATA, $data);
    }
}
