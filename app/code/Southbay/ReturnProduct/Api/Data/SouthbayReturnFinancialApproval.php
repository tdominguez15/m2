<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnFinancialApproval
{
    const TABLE = 'southbay_return_financial_approval';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_RETURN_ID = 'southbay_return_id';
    const ENTITY_COUNTRY_CODE = self::TABLE . '_country_code';
    const ENTITY_APPROVED = self::TABLE . '_approved';
    const ENTITY_USER_CODE = self::TABLE . '_user_code';
    const ENTITY_USER_NAME = self::TABLE . '_user_name';

    const ENTITY_TOTAL_ACCEPTED_AMOUNT = self::TABLE . '_total_accepted_amount';
    const ENTITY_TOTAL_ACCEPTED = self::TABLE . '_total_accepted';
    const ENTITY_TOTAL_VALUED_AMOUNT = self::TABLE . '_total_valued_amount';
    const ENTITY_EXCHANGE_RATE = self::TABLE . '_exchange_rate';
    const ENTITY_TOTAL_APPROVALS = self::TABLE . '_total_approvals';
    const ENTITY_TOTAL_PENDING_APPROVALS = self::TABLE . '_total_pending_approvals';
    const ENTITY_REQUIRE_ALL_MEMBERS = 'require_all_members';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function setCountryCode($value);

    public function getCountryCode();

    public function getReturnId();

    public function setReturnId($value);

    public function getApproved();

    public function setApproved($value);

    public function getUserCode();

    public function setUserCode($value);

    public function getUserName();

    public function setUserName($value);

    public function getTotalAcceptedAmount();

    public function setTotalAcceptedAmount($value);

    public function getTotalAccepted();

    public function setTotalAccepted($value);

    public function setTotalValuedAmount($value);

    public function getTotalValuedAmount();

    public function setExchangeRate($value);

    public function getExchangeRate();

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);

    public function getTotalApprovals();

    public function setTotalApprovals($value);

    public function getTotalPendingApprovals();

    public function setTotalPendingApprovals($value);

    public function getRequireAllMembers();

    public function setRequireAllMembers($value);
}
