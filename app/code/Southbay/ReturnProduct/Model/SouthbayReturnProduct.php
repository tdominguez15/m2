<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct as SouthbayReturnProductInterfase;

class SouthbayReturnProduct extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnProductInterfase
{
    protected $_cacheTag = SouthbayReturnProductInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayReturnProductInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProduct');
    }

    public function getIdentities(): array
    {
        return [SouthbayReturnProductInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getType()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_TYPE);
    }

    public function setType($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_TYPE, $value);
    }

    public function getCountryCode()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_COUNTRY_CODE, $value);
    }

    public function setCreatedAt($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_UPDATED_AT);
    }

    public function getUserCode()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_USER_CODE);
    }

    public function setUserCode($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_USER_CODE, $value);
    }

    public function getUserName()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_USER_NAME);
    }

    public function setUserName($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_USER_NAME, $value);
    }

    public function getStatus()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_STATUS);
    }

    public function setStatus($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_STATUS, $value);
    }

    public function getStatusName()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_STATUS_NAME);
    }

    public function setStatusName($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_STATUS_NAME, $value);
    }

    public function getTotalReturn()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_TOTAL_RETURN);
    }

    public function setTotalReturn($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_TOTAL_RETURN, $value);
    }

    public function getTotalAmount()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT);
    }

    public function setTotalAmount($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT, $value);
    }

    public function getTotalAmountAccepted()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT_ACCEPTED);
    }

    public function setTotalAmountAccepted($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT_ACCEPTED, $value);
    }

    public function getTotalAccepted()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_TOTAL_ACCEPTED);
    }

    public function setTotalAccepted($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_TOTAL_ACCEPTED, $value);
    }

    public function getTotalRejected()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_TOTAL_REJECTED);
    }

    public function setTotalRejected($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_TOTAL_REJECTED, $value);
    }

    public function getTotalAmountRejected()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT_REJECTED);
    }

    public function setTotalAmountRejected($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT_REJECTED, $value);
    }

    public function isPrinted()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_PRINTED);
    }

    public function setPrinted($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_PRINTED, $value);
    }

    public function getPrintedAt()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_PRINTED_AT);
    }

    public function setPrintedAt($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_PRINTED_AT, $value);
    }

    public function getLabelTotalPackages()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_LABEL_TOTAL_PACKAGES);
    }

    public function setLabelTotalPackages($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_LABEL_TOTAL_PACKAGES, $value);
    }

    public function getCustomerCode()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_CODE);
    }

    public function setCustomerCode($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_CODE, $value);
    }

    public function getCustomerName()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_NAME);
    }

    public function setCustomerName($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_NAME, $value);
    }

    public function getUserConfirmCode()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_CODE);
    }

    public function setUserConfirmCode($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_CODE, $value);
    }

    public function getUserConfirmName()
    {
        return $this->getData(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_NAME);
    }

    public function setUserConfirmName($value)
    {
        $this->setData(SouthbayReturnProductInterfase::ENTITY_USER_CONFIRM_NAME, $value);
    }
}
