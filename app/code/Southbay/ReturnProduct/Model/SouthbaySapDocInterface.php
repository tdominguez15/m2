<?php

namespace Southbay\ReturnProduct\Model;

use Magento\Framework\Model\AbstractModel;
use Southbay\ReturnProduct\Api\Data\SouthbaySapDocInterface as ModelInterface;

class SouthbaySapDocInterface extends AbstractModel implements ModelInterface
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\SouthbaySapDocInterface::class);
    }

    /**
     * Get map country ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * Get update time.
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    public function getResultMsg()
    {
        $this->getData(self::RESULT_MSG);
    }

    public function setResultMsg($value)
    {
        return $this->setData(self::RESULT_MSG, $value);
    }

    public function getRawData()
    {
        return $this->getData(self::DATA);
    }

    public function setRawData($value)
    {
        return $this->setData(self::DATA, $value);
    }

    public function getStartImportDate()
    {
        return $this->getData(self::START_IMPORT_DATE);
    }

    public function setStartImportDate($value)
    {
        return $this->setData(self::START_IMPORT_DATE, $value);
    }
    public function getEndImportDate()
    {
        return $this->getData(self::END_IMPORT_DATE);
    }
    public function setEndImportDate($value)
    {
        return $this->setData(self::END_IMPORT_DATE, $value);
    }

    public function getRetryAt()
    {
        return $this->getData(self::RETRY_AT);
    }

    public function setRetryAt($value)
    {
        $this->setData(self::RETRY_AT, $value);
    }

    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    public function setType($value)
    {
        $this->setData(self::TYPE, $value);
    }
}
