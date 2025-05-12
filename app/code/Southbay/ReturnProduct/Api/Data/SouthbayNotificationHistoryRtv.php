<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayNotificationHistoryRtv
{
    const TABLE = 'southbay_notification_history_rtv';
    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_COUNTRY_CODE = self::TABLE . '_country_code';
    const ENTITY_TEMPLATE_CODE = self::TABLE . '_template_code';
    const ENTITY_RETURN_ID = 'southbay_return_id';
    const ENTITY_CUSTOMER_CODE = 'southbay_return_customer_code';
    const ENTITY_TYPE = 'southbay_return_type';
    const ENTITY_STATUS = 'southbay_return_status';

    const ENTITY_SUBJECT = self::TABLE . '_subject';
    const ENTITY_CONTENT = self::TABLE . '_content';
    const ENTITY_FROM = self::TABLE . '_from';
    const ENTITY_TO = self::TABLE . '_to';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();

    public function setCountryCode($value);

    public function getCustomerCode();

    public function setCustomerCode($value);

    public function getType();

    public function setType($value);

    public function getReturnId();

    public function setReturnId($value);

    public function getTemplateCode();

    public function setTemplateCode($value);

    public function getStatus();

    public function setStatus($value);

    public function getFrom();

    public function setFrom($value);

    public function getTo();

    public function setTo($value);

    public function getSubject();

    public function setSubject($value);

    public function getContent();

    public function setContent($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
