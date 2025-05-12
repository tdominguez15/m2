<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayExchangeReturn
{
    const TABLE = 'southbay_exchange_return';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_COUNTRY_CODE = self::TABLE . '_country_code';
    const ENTITY_EXCHANGE = self::TABLE . '_exchange';
    const ENTITY_USER_CODE = self::TABLE . '_user_code';
    const ENTITY_USER_NAME = self::TABLE . '_user_name';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();

    public function setCountryCode($value);

    public function getExchange();

    public function setExchange($value);

    public function getUserCode();
    public function setUserCode($value);

    public function getUserName();
    public function setUserName($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
