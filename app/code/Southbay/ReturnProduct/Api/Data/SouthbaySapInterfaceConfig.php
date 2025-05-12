<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbaySapInterfaceConfig
{
    const TABLE = 'southbay_sap_interface_config';
    const TYPE_INNOVA = 'innova';
    const TYPE_NO_INNOVA = 'no_innova';
    const TYPE_PURCHASE_ORDER = 'purchase_order';
    const TYPE_CHECK_STATUS = 'check_status';
    const TYPE_STOCK_ATP = 'stock_atp';

    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';

    const ENTITY_TYPE = self::TABLE . '_type';
    const ENTITY_URL = self::TABLE . '_url';

    const ENTITY_USERNAME = self::TABLE . '_user';

    const ENTITY_PASSWORD = self::TABLE . '_pass';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getType();

    public function setType($value);

    public function getUsername();

    public function setUsername($value);

    public function getPassword();

    public function setPassword($value);

    public function getUrl();

    public function setUrl($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
