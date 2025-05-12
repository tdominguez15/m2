<?php

namespace Southbay\Product\Model;

use Magento\Framework\Model\AbstractModel;
use Southbay\Product\Api\Data\ProductSapInterface as ModelEntity;

class ProductSapInterface extends AbstractModel implements ModelEntity
{
    protected function _construct()
    {
        $this->_init(\Southbay\Product\Model\ResourceModel\ProductSapInterface::class);
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

    public function getRawData()
    {
        return $this->getData(self::ENTITY_RAW_DATA);
    }

    public function setRawData($value)
    {
        $this->setData(self::ENTITY_RAW_DATA, $value);
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

    public function setEndDate($value)
    {
        $this->setData(self::ENTITY_END_IMPORT_DATE, $value);
    }

    public function getEndDate()
    {
        return $this->getData(self::ENTITY_END_IMPORT_DATE);
    }

    public function setStartDate($value)
    {
        $this->setData(self::ENTITY_START_IMPORT_DATE, $value);
    }

    public function getStartDate()
    {
        return $this->getData(self::ENTITY_START_IMPORT_DATE);
    }
}
