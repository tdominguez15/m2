<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayRolConfigRtv
{
    const TABLE = 'southbay_rol_config_return';
    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_COUNTRY_CODE = self::TABLE . '_country_code';
    const ENTITY_TYPE = self::TABLE . '_type';
    const ENTITY_TYPE_ROL = self::TABLE . '_type_rol';
    const ENTITY_ROL_CODE = self::TABLE . '_rol_code';
    const ENTITY_APPROVAL_USE_AMOUNT_LIMIT = self::TABLE . '_approval_use_amount_limit';
    const ENTITY_APPROVAL_AMOUNT_LIMIT = self::TABLE . '_approval_amount_limit';
    const ENTITY_REQUIRE_ALL_MEMBERS = 'require_all_members';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();

    public function setCountryCode($value);

    public function getType();

    public function setType($value);

    public function getTypeRol();

    public function setTypeRol($value);

    public function getRolCode();

    public function setRolCode($value);

    public function getApprovalUseAmountLimit();

    public function setApprovalUseAmountLimit($value);

    public function getApprovalAmountLimit();

    public function setApprovalAmountLimit($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();
    public function setRequireAllMembers($value);

    public function getRequireAllMembers();

    public function getData($key = '', $index = null);
}
