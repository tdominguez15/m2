<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReasonReturn
{
    const TABLE = 'southbay_reason_return';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_COUNTRY_CODE = 'southbay_reason_return_country_code';
    const ENTITY_CODE = 'southbay_reason_return_code';
    const ENTITY_NAME = 'southbay_reason_return_name';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();
    public function setCountryCode($value);


    public function getCode();
    public function setCode($value);

    public function getName();
    public function setName($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
