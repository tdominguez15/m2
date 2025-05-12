<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbaySapCheckStatus
{
    const TABLE = 'southbay_sap_check_status';
    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_SAP_INTERFACE_ID = 'southbay_sap_interface_id';
    const ENTITY_RESPONSE = 'southbay_sap_interface_response';
    const ENTITY_CHECK_SUM = 'southbay_sap_interface_check_sum';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getResponse();

    public function setResponse($value);

    public function setCheckSum($value);

    public function getCheckSum();

    public function setSapInterfaceId($value);

    public function getSapInterfaceId();

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
