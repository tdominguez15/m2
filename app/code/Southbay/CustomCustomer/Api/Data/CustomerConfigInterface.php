<?php

namespace Southbay\CustomCustomer\Api\Data;

interface CustomerConfigInterface
{
    const TABLE = 'southbay_customer_config';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'southbay_customer_config_id';
    const ENTITY_MAGENTO_CUSTOMER_EMAIL = 'magento_customer_email';
    const ENTITY_SOLD_TO_IDS = 'southbay_customer_config_sold_to_ids';
    const ENTITY_COUNTRIES_CODES = 'southbay_customer_config_countries_codes';
    const ENTITY_FUNCTIONS_CODES = 'southbay_customer_config_functions_codes';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getMagentoCustomerEmail();
    public function setMagentoCustomerEmail($value);

    public function getSoldToIds();
    public function setSoldToIds($value);

    public function getCountriesCodes();
    public function setCountriesCodes($value);

    public function getFunctionsCodes();
    public function setFunctionsCodes($value);


    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
