<?php

namespace Southbay\Product\Model;

class SouthbayProductImportImgHistory extends \Magento\Framework\Model\AbstractModel implements \Southbay\Product\Api\Data\SouthbayProductImportImgHistoryInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
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

    public function getTotalFiles()
    {
        return $this->getData(self::ENTITY_TOTAL_FILES);
    }

    public function setTotalFiles($total)
    {
        $this->setData(self::ENTITY_TOTAL_FILES, $total);
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

    public function setName($name)
    {
        $this->setData(self::ENTITY_NAME, $name);
    }

    public function getName()
    {
        return $this->getData(self::ENTITY_NAME);
    }
}
