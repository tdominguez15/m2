<?php

namespace Southbay\Product\Model;

class SouthbayProductGroup extends \Magento\Framework\Model\AbstractModel implements \Southbay\Product\Api\Data\SouthbayProductGroupInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbayProductGroup');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    public function setType($type)
    {
        $this->setData(self::ENTITY_TYPE, $type);
    }

    public function getCode()
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function setCode($code)
    {
        $this->setData(self::ENTITY_CODE, $code);
    }

    public function getName()
    {
        return $this->getData(self::ENTITY_NAME);
    }

    public function setName($name)
    {
        $this->setData(self::ENTITY_NAME, $name);
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
}
