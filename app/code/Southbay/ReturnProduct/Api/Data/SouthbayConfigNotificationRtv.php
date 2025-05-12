<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayConfigNotificationRtv
{
    const TABLE = 'southbay_config_notification_rtv';
    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_TYPE = self::TABLE . '_return_type';
    const ENTITY_COUNTRY_CODE = self::TABLE . '_country_code';
    const ENTITY_TEMPLATE_CODE = self::TABLE . '_template_code';
    const ENTITY_STATUS = self::TABLE . '_status';

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

    public function getTemplateCode();

    public function setTemplateCode($value);

    public function getStatus();

    public function setStatus($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
