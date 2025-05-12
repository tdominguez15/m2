<?php

namespace Southbay\Product\Model;

class Season extends \Magento\Framework\Model\AbstractModel implements \Southbay\Product\Api\Data\SeasonInterface
{
    const CACHE_TAG = self::TABLE;

    protected $_cacheTag = Season::CACHE_TAG;

    protected $_eventPrefix = Season::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\Season');
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


    public function getSeasonCode()
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function setSeasonCode($code)
    {
        $this->setData(self::ENTITY_CODE, $code);
    }

    public function getSeasonName()
    {
        return $this->getData(self::ENTITY_NAME);
    }

    public function setSeasonName($name)
    {
        $this->setData(self::ENTITY_NAME, $name);
    }

    public function getSeasonCategoryId()
    {
        return $this->getData(self::ENTITY_SEASON_CATEGORY_ID);
    }

    public function setSeasonCategoryId($seasonCategoryId)
    {
        $this->setData(self::ENTITY_SEASON_CATEGORY_ID, $seasonCategoryId);
    }

    public function getSeasonDescription()
    {
        return $this->getData(self::ENTITY_SEASON_DESCRIPTION);
    }

    public function setSeasonDescription($seasonDescription)
    {
        $this->setData(self::ENTITY_SEASON_DESCRIPTION, $seasonDescription);
    }

    public function getMonthDeliveryDate1()
    {
        return $this->getData(self::ENTITY_SEASON_MONTH_DELIVERY_DATE_1);
    }

    public function setMonthDeliveryDate1($monthDeliveryDate1)
    {
        $this->setData(self::ENTITY_SEASON_MONTH_DELIVERY_DATE_1, $monthDeliveryDate1);
    }

    public function getMonthDeliveryDate2()
    {
        return $this->getData(self::ENTITY_SEASON_MONTH_DELIVERY_DATE_2);
    }

    public function setMonthDeliveryDate2($monthDeliveryDate2)
    {
        $this->setData(self::ENTITY_SEASON_MONTH_DELIVERY_DATE_2, $monthDeliveryDate2);
    }

    public function getMonthDeliveryDate3()
    {
        return $this->getData(self::ENTITY_SEASON_MONTH_DELIVERY_DATE_3);
    }

    public function setMonthDeliveryDate3($monthDeliveryDate3)
    {
        $this->setData(self::ENTITY_SEASON_MONTH_DELIVERY_DATE_3, $monthDeliveryDate3);
    }

    public function getSeasonTypeCode()
    {
        return $this->getData(self::ENTITY_SEASON_TYPE_CODE);
    }

    public function setSeasonTypeCode($seasonTypeCode)
    {
        $this->setData(self::ENTITY_SEASON_TYPE_CODE, $seasonTypeCode);
    }

    public function getCountryCode()
    {
        return $this->getData(self::ENTITY_SEASON_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(self::ENTITY_SEASON_COUNTRY_CODE, $value);
    }

    public function getStoreId()
    {
        return $this->getData(self::ENTITY_SEASON_STORE_ID);
    }

    public function setStoreId($value)
    {
        $this->setData(self::ENTITY_SEASON_STORE_ID, $value);
    }

    public function getStartAt()
    {
        return $this->getData(self::ENTITY_SEASON_START_AT);
    }

    public function setStartAt($value)
    {
        $this->setData(self::ENTITY_SEASON_START_AT, $value);
    }

    public function getEndAt()
    {
        return $this->getData(self::ENTITY_SEASON_END_AT);
    }

    public function setEndAt($value)
    {
        $this->setData(self::ENTITY_SEASON_END_AT, $value);
    }

    public function setActive($value)
    {
        $this->setData(self::ENTITY_SEASON_ACTIVE, $value);
    }

    public function getActive()
    {
        return $this->getData(self::ENTITY_SEASON_ACTIVE);
    }
}
