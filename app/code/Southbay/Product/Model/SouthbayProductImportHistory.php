<?php

namespace Southbay\Product\Model;

class SouthbayProductImportHistory extends \Magento\Framework\Model\AbstractModel implements \Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getSeasonId()
    {
        return $this->getData(self::ENTITY_SEASON_ID);
    }

    public function setSeasonId($seasonId)
    {
        $this->setData(self::ENTITY_SEASON_ID, $seasonId);
    }

    public function getStatus()
    {
        return $this->getData(self::ENTITY_STATUS);
    }

    public function setStatus($status)
    {
        $this->setData(self::ENTITY_STATUS, $status);
    }

    public function getResultMsg()
    {
        return $this->getData(self::ENTITY_RESULT_MSG);
    }

    public function setResultMsg($resultMsg)
    {
        $this->setData(self::ENTITY_RESULT_MSG, $resultMsg);
    }

    public function getStartImportDate()
    {
        return $this->getData(self::ENTITY_START_IMPORT_DATE);
    }

    public function setStartImportDate($startImportDate)
    {
        $this->setData(self::ENTITY_START_IMPORT_DATE, $startImportDate);
    }

    public function getEndImportDate()
    {
        return $this->getData(self::ENTITY_END_IMPORT_DATE);
    }

    public function setEndImportDate($endImportDate)
    {
        $this->setData(self::ENTITY_END_IMPORT_DATE, $endImportDate);
    }

    public function getLines()
    {
        return $this->getData(self::ENTITY_LINES);
    }

    public function setLines($lines)
    {
        $this->setData(self::ENTITY_LINES, $lines);
    }

    public function getStartOnLineNumber()
    {
        return $this->getData(self::ENTITY_START_ON_LINE_NUMBER);
    }

    public function setStartOnLineNumber($startOnLineNumber)
    {
        $this->setData(self::ENTITY_START_ON_LINE_NUMBER, $startOnLineNumber);
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

    public function setFile($name)
    {
        $this->setData(self::ENTITY_FILE, $name);
    }

    public function getFile()
    {
        return $this->getData(self::ENTITY_FILE);
    }

    public function getSkus()
    {
        return $this->getData(self::ENTITY_SKUS);
    }

    public function setSkus($skus)
    {
        $this->setData(self::ENTITY_SKUS, $skus);
    }

    public function getStoreId()
    {
        return $this->getData(self::ENTITY_STORE_ID);
    }

    public function setStoreId($storeId)
    {
        $this->setData(self::ENTITY_STORE_ID, $storeId);
    }

    public function getIsAtOnce()
    {
        return $this->getData(self::ENTITY_IS_AT_ONCE);
    }

    public function setIsAtOnce($isAtOnce)
    {
        $this->setData(self::ENTITY_IS_AT_ONCE, $isAtOnce);
    }

    public function getType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    public function setType($value)
    {
        $this->setData(self::ENTITY_TYPE, $value);
    }

    public function getAttributeCode()
    {
        return $this->getData(self::ENTITY_ATTRIBUTE_CODE);
    }

    public function setAttributeCode($value)
    {
        $this->setData(self::ENTITY_ATTRIBUTE_CODE, $value);
    }

    public function getTypeOperation()
    {
        return $this->getData(self::ENTITY_TYPE_OPERATION);
    }

    public function setTypeOperation($value)
    {
        $this->setData(self::ENTITY_TYPE_OPERATION, $value);
    }
}
