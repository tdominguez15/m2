<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbaySapInterface
{
    const TABLE = 'southbay_sap_interface';
    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';

    const STATUS_INIT = 'init';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    const ENTITY_STATUS = self::TABLE . '_status';
    const ENTITY_FROM = self::TABLE . '_from';
    const ENTITY_REF = self::TABLE . '_ref';
    const ENTITY_END = self::TABLE . '_end';
    const ENTITY_URL = self::TABLE . '_url';
    const ENTITY_REQUEST = self::TABLE . '_request';
    const ENTITY_RESPONSE = self::TABLE . '_response';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getStatus();

    public function setStatus($value);

    public function getUrl();

    public function setUrl($value);

    public function getFrom();

    public function setFrom($value);

    public function getRef();

    public function setRef($value);

    public function getRequest();

    public function setRequest($value);

    public function getResponse();

    public function setResponse($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function setEnd($value);

    public function getEnd();

    public function getData($key = '', $index = null);
}
