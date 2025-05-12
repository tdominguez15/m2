<?php

namespace Southbay\Product\Model;

class SeasonType extends \Magento\Framework\Model\AbstractModel implements \Southbay\Product\Api\Data\SeasonTypeInterface
{
    const CACHE_TAG = 'southbay_season_type';

    protected $_cacheTag = SeasonType::CACHE_TAG;

    protected $_eventPrefix = SeasonType::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SeasonType');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
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

    public function getSeasonTypeCode()
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function setSeasonTypeCode($code)
    {
        $this->setData(self::ENTITY_CODE, $code);
    }

    public function getSeasonTypeName()
    {
        return $this->getData(self::ENTITY_NAME);
    }

    public function setSeasonTypeName($name)
    {
        $this->setData(self::ENTITY_NAME, $name);
    }
}
