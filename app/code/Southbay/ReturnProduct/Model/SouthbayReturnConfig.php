<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig as SouthbayReturnConfigInterfase;

class SouthbayReturnConfig extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnConfigInterfase
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnConfig');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }


    public function setCreatedAt($value)
    {
        $this->setData(self::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(self::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }


    public function getCountryCode()
    {
        return $this->getData(self::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(self::ENTITY_COUNTRY_CODE, $value);
    }

    public function getType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    public function setType($value)
    {
        $this->setData(self::ENTITY_TYPE, $value);
    }

    public function getMaxYearHistory()
    {
        return $this->getData(self::ENTITY_MAX_YEAR_HISTORY);
    }

    public function setMaxYearHistory($value)
    {
        $this->setData(self::ENTITY_MAX_YEAR_HISTORY, $value);
    }

    public function getOrder()
    {
        return $this->getData(self::ENTITY_ORDER);
    }

    public function setOrder($value)
    {
        $this->setData(self::ENTITY_ORDER, $value);
    }

    public function getLabelText()
    {
        return $this->getData(self::ENTITY_LABEL_TEXT);
    }

    public function setLabelText($value)
    {
        $this->setData(self::ENTITY_LABEL_TEXT, $value);
    }

    public function getAvailableAutomaticApproval()
    {
        return $this->getData(self::ENTITY_AVAILABLE_AUTOMATIC_APPROVAL);
    }

    public function SetAvailableAutomaticApproval($value)
    {
        $this->setData(self::ENTITY_AVAILABLE_AUTOMATIC_APPROVAL, $value);
    }

    public function getMaxAutomaticAmount()
    {
        return $this->getData(self::ENTITY_MAX_AUTOMATIC_AMOUNT);
    }

    public function setMaxAutomaticAmount($value)
    {
        $this->setData(self::ENTITY_MAX_AUTOMATIC_AMOUNT, $value);
    }
}
