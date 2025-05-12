<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval as SouthbayReturnFinancialApprovalInterfase;

class SouthbayReturnFinancialApproval extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnFinancialApprovalInterfase
{
    protected $_cacheTag = SouthbayReturnFinancialApprovalInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayReturnFinancialApprovalInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApproval');
    }

    public function getIdentities(): array
    {
        return [SouthbayReturnFinancialApprovalInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function setCountryCode($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_COUNTRY_CODE, $value);
    }

    public function getCountryCode()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_COUNTRY_CODE);
    }


    public function setCreatedAt($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_UPDATED_AT);
    }


    public function getReturnId()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_RETURN_ID);
    }

    public function setReturnId($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_RETURN_ID, $value);
    }

    public function getApproved()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_APPROVED);
    }

    public function setApproved($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_APPROVED, $value);
    }

    public function getUserCode()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_UPDATED_AT);
    }

    public function setUserCode($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_USER_CODE, $value);
    }

    public function getUserName()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_USER_NAME);
    }

    public function setUserName($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_USER_NAME, $value);
    }

    public function getTotalAcceptedAmount()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_ACCEPTED_AMOUNT);
    }

    public function setTotalAcceptedAmount($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_ACCEPTED_AMOUNT, $value);
    }

    public function getTotalAccepted()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_ACCEPTED);
    }

    public function setTotalAccepted($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_ACCEPTED, $value);
    }

    public function setTotalValuedAmount($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_VALUED_AMOUNT, $value);
    }

    public function getTotalValuedAmount()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_VALUED_AMOUNT);
    }

    public function setExchangeRate($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_EXCHANGE_RATE, $value);
    }

    public function getExchangeRate()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_EXCHANGE_RATE);
    }

    public function getTotalApprovals()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_APPROVALS);
    }

    public function setTotalApprovals($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_APPROVALS, $value);
    }

    public function getTotalPendingApprovals()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_PENDING_APPROVALS);
    }

    public function setTotalPendingApprovals($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_PENDING_APPROVALS, $value);
    }

    public function getRequireAllMembers()
    {
        return $this->getData(SouthbayReturnFinancialApprovalInterfase::ENTITY_REQUIRE_ALL_MEMBERS);
    }

    public function setRequireAllMembers($value)
    {
        $this->setData(SouthbayReturnFinancialApprovalInterfase::ENTITY_REQUIRE_ALL_MEMBERS, $value);
    }
}
