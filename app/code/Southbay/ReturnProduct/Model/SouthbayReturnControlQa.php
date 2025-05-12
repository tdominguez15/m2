<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa as SouthbayReturnControlQaInterfase;

class SouthbayReturnControlQa extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnControlQaInterfase
{
    protected $_cacheTag = SouthbayReturnControlQaInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayReturnControlQaInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQa');
    }

    public function getIdentities(): array
    {
        return [SouthbayReturnControlQaInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }


    public function setCreatedAt($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_UPDATED_AT);
    }

    public function getCountryCode()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_COUNTRY_CODE, $value);
    }

    public function getReturnId()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_RETURN_ID);
    }

    public function setReturnId($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_RETURN_ID, $value);
    }

    public function getUserCode()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_USER_CODE);
    }

    public function setUserCode($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_USER_CODE, $value);
    }

    public function getUserName()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_USER_NAME);
    }

    public function setUserName($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_USER_NAME, $value);
    }

    public function setTotalReal($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_REAL, $value);
    }

    public function getTotalReal()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_REAL);
    }

    public function setTotalExtra($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_EXTRA, $value);
    }

    public function getTotalExtra()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_EXTRA);
    }

    public function setTotalMissing($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_MISSING, $value);
    }

    public function getTotalMissing()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_MISSING);
    }

    public function setTotalAccepted($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_ACCEPTED, $value);
    }

    public function getTotalAccepted()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_ACCEPTED);
    }

    public function setTotalRejected($value)
    {
        $this->setData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_REJECT, $value);
    }

    public function getTotalRejected()
    {
        return $this->getData(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_REJECT);
    }
}
