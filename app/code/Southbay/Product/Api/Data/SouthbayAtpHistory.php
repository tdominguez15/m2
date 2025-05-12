<?php

namespace Southbay\Product\Api\Data;

interface SouthbayAtpHistory
{
    const TABLE = 'southbay_atp_history';

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const ENTITY_COUNTRY_CODE = 'country_code';
    const ENTITY_SAP_COUNTRY_CODE = 'sap_country_code';
    const ENTITY_JSON_DATA = 'json';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();

    public function setCountryCode($data);

    public function getSapCountryCode();

    public function setSapCountryCode($data);

    public function getJsonData();

    public function setJsonData($data);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
