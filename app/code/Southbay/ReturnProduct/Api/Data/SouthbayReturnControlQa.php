<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnControlQa
{
    const TABLE = 'southbay_return_control_qa';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_RETURN_ID = 'southbay_return_id';
    const ENTITY_COUNTRY_CODE = self::TABLE . '_country_code';
    const ENTITY_USER_CODE = self::TABLE . '_user_code';
    const ENTITY_USER_NAME = self::TABLE . '_user_name';
    const ENTITY_TOTAL_REAL = self::TABLE . '_total_real';
    const ENTITY_TOTAL_MISSING = self::TABLE . '_total_missing';
    const ENTITY_TOTAL_EXTRA = self::TABLE . '_total_extra';

    const ENTITY_TOTAL_ACCEPTED = self::TABLE . '_total_accepted';
    const ENTITY_TOTAL_REJECT = self::TABLE . '_total_reject';

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

    public function getUserCode();

    public function setUserCode($value);

    public function getUserName();

    public function setUserName($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function setTotalReal($value);

    public function getTotalReal();

    public function setTotalExtra($value);

    public function getTotalExtra();

    public function setTotalMissing($value);

    public function getTotalMissing();

    public function setTotalAccepted($value);

    public function getTotalAccepted();

    public function setTotalRejected($value);

    public function getTotalRejected();

    public function getData($key = '', $index = null);
}
