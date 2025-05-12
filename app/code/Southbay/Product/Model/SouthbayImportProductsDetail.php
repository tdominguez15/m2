<?php

namespace Southbay\Product\Model;

use Southbay\Product\Api\Data\SouthbayImportProductsDetail as EntityInterface;

class SouthbayImportProductsDetail extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbayImportProductsDetail');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getSeasonId()
    {
        return $this->getData(self::ENTITY_SEASON_ID);
    }

    public function setSeasonId($value)
    {
        $this->setData(self::ENTITY_SEASON_ID, $value);
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

    public function getLine()
    {
        return $this->getData(self::ENTITY_LINE);
    }

    public function setLine($value)
    {
        $this->setData(self::ENTITY_LINE, $value);
    }

    public function getStatus()
    {
        return $this->getData(self::ENTITY_STATUS);
    }

    public function setStatus($value)
    {
        $this->setData(self::ENTITY_STATUS, $value);
    }

    public function getResultMsg()
    {
        return $this->getData(self::ENTITY_RESULT_MSG);
    }

    public function setResultMsg($value)
    {
        $this->setData(self::ENTITY_RESULT_MSG, $value);
    }

    public function getSourceData()
    {
        return $this->getData(self::ENTITY_SOURCE_DATA);
    }

    public function setSourceData($value)
    {
        $this->setData(self::ENTITY_SOURCE_DATA, $value);
    }

    public function getProcessData()
    {
        return $this->getData(self::ENTITY_PROCESS_DATA);
    }

    public function setProcessData($value)
    {
        $this->setData(self::ENTITY_PROCESS_DATA, $value);
    }

    public function getStartImportDate()
    {
        return $this->getData(self::ENTITY_START_IMPORT_DATE);
    }

    public function setStartImportDate($value)
    {
        $this->setData(self::ENTITY_START_IMPORT_DATE, $value);
    }

    public function getEndImportDate()
    {
        return $this->getData(self::ENTITY_END_IMPORT_DATE);
    }

    public function setEndImportDate($value)
    {
        $this->setData(self::ENTITY_END_IMPORT_DATE, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt($value)
    {
        $this->setData(self::ENTITY_CREATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(self::ENTITY_UPDATED_AT, $value);
    }
}
