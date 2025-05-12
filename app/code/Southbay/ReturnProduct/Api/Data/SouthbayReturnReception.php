<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnReception
{
    const TABLE = 'southbay_return_reception';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_COUNTRY_CODE = self::TABLE . '_country_code';
    const ENTITY_RETURN_ID = 'southbay_return_id';
    const ENTITY_USER_CODE = self::TABLE . '_user_code';
    const ENTITY_USER_NAME = self::TABLE . '_user_name';
    const ENTITY_TOTAL_PACKAGES = self::TABLE . '_total_packages';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setCountryCode($value);
    public function getCountryCode();

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getReturnId();
    public function setReturnId($value);

    public function getUserCode();
    public function setUserCode($value);

    public function getUserName();
    public function setUserName($value);

    public function getTotalPackages();
    public function setTotalPackages($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
