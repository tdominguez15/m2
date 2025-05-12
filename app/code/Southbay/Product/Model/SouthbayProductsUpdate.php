<?php

namespace Southbay\Product\Model;

use Southbay\Product\Api\Data\SouthbayProductsUpdate as EntityInterface;

class SouthbayProductsUpdate extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbayProductsUpdate');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getSeasonImportId()
    {
        return $this->getData(self::ENTITY_SEASON_IMPORT_ID);
    }

    public function setSeasonImportId($value)
    {
        $this->setData(self::ENTITY_SEASON_IMPORT_ID, $value);
    }

    public function getSku()
    {
        return $this->getData(self::ENTITY_SKU);
    }

    public function setSku($value)
    {
        $this->setData(self::ENTITY_SKU, $value);
    }

    public function getProductId()
    {
        return $this->getData(self::ENTITY_PRODUCT_ID);
    }

    public function setProductId($value)
    {
        $this->setData(self::ENTITY_PRODUCT_ID, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt($value)
    {
        $this->setData(self::ENTITY_CREATED_AT, $value);
    }
}
