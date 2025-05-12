<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayInvoice
{
    const TABLE = 'southbay_invoice';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'southbay_invoice_id';
    const ENTITY_COUNTRY_CODE = 'southbay_invoice_country_code';
    const ENTITY_OLD_INVOICE = 'southbay_old_invoice';
    const ENTITY_CUSTOMER_CODE = 'southbay_customer_code';
    const ENTITY_CUSTOMER_NAME = 'southbay_customer_name';
    const ENTITY_CUSTOMER_SHIP_TO_CODE = 'southbay_customer_ship_to_code';
    const ENTITY_CUSTOMER_SHIP_TO_NAME = 'southbay_customer_ship_to_name';
    const ENTITY_INVOICE_DATE = 'southbay_invoice_date';
    const ENTITY_INTERNAL_INVOICE_NUMBER = 'southbay_int_invoice_num';
    const ENTITY_INVOICE_REF = 'southbay_invoice_ref';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();

    public function setCountryCode($value);


    public function getOldInvoice();

    public function setOldInvoice($value);

    public function setCustomerCode($value);

    public function getCustomerCode();

    public function setCustomerName($value);

    public function getCustomerName();

    public function setCustomerShipToCode($value);

    public function getCustomerShipToCode();

    public function setCustomerShipToName($value);

    public function getCustomerShipToName();

    public function setDivCode($value);

    public function getDivCode();

    public function setInvoiceDate($value);

    public function getInvoiceDate();

    public function setIntInvoiceNum($value);

    public function getIntInvoiceNum();

    public function setInvoiceRef($value);

    public function getInvoiceRef();

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
