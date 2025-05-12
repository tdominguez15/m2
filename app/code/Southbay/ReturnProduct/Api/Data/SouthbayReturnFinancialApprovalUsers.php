<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnFinancialApprovalUsers
{
    const TABLE = 'southbay_return_financial_approval_users';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const ENTITY_RETURN_ID = 'southbay_return_id';

    const ENTITY_USER_CODE = 'user_code';
    const ENTITY_USER_NAME = 'username';
    const ENTITY_ROL_CODE = 'rol_code';
    const ENTITY_APPROVED = 'approved';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getReturnId();

    public function setReturnId($value);

    public function getApproved();

    public function setApproved($value);

    public function getUserCode();

    public function setUserCode($value);

    public function getUserName();

    public function setUserName($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);

    public function getRolCode();

    public function setRolCode($value);
}
