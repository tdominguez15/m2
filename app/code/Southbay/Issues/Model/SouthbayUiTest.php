<?php

namespace Southbay\Issues\Model;

use Southbay\Issues\Api\Data\SouthbayUiTest as EntityInterface;

class SouthbayUiTest extends \Magento\Framework\Model\AbstractModel implements EntityInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Issues\Model\ResourceModel\SouthbayUiTest');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getName()
    {
        return $this->getData(self::ENTITY_NAME);
    }

    public function setName($value)
    {
        $this->setData(self::ENTITY_NAME, $value);
    }

    public function getDescription()
    {
        return $this->getData(self::ENTITY_DESCRIPTION);
    }

    public function setDescription($value)
    {
        $this->setData(self::ENTITY_DESCRIPTION, $value);
    }

    public function getContent()
    {
        return $this->getData(self::ENTITY_CONTENT);
    }

    public function setContent($value)
    {
        $this->setData(self::ENTITY_CONTENT, $value);
    }

    public function getResult()
    {
        return $this->getData(self::ENTITY_RESULT);
    }

    public function setResult($value)
    {
        $this->setData(self::ENTITY_RESULT, $value);
    }

    public function getTotalExecution()
    {
        return $this->getData(self::ENTITY_TOTAL_EXECUTION);
    }

    public function setTotalExecution($value)
    {
        $this->setData(self::ENTITY_TOTAL_EXECUTION, $value);
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
