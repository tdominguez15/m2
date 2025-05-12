<?php

namespace Southbay\CustomCustomer\Api\Data;

interface SoldToInterface
{
    const TABLE = 'southbay_sold_to';
    const SOUTHBAY_SOLD_TO_ID = 'southbay_sold_to_id';
    const SOUTHBAY_SOLD_TO_COUNTRY_CODE = 'southbay_sold_to_country_code';
    const SOUTHBAY_SOLD_TO_SAP_COUNTRY_CODE = 'southbay_sold_to_sap_country_code';
    const SOUTHBAY_SOLD_TO_CHANNEL_CODE = 'southbay_sold_to_channel_code';
    const SOUTHBAY_SOLD_TO_SECTION_CODE = 'southbay_sold_to_section_code';
    const SOUTHBAY_SOLD_TO_CUSTOMER_CODE = 'southbay_sold_to_customer_code';
    const SOUTHBAY_SOLD_TO_OLD_CUSTOMER_CODE = 'southbay_sold_to_customer_code_old';
    const SOUTHBAY_SOLD_TO_COUNTRY_BUSINESS_CODE = 'southbay_sold_to_country_business_code';
    const SOUTHBAY_SOLD_TO_CUSTOMER_NAME = 'southbay_sold_to_customer_name';
    const SOUTHBAY_SOLD_TO_LOCKED = 'southbay_sold_to_locked';
    const SOUTHBAY_SEGMENTATION = 'southbay_sold_to_segmentation';
    const SOUTHBAY_IS_INTERNAL = 'southbay_sold_to_is_internal';
    const SOUTHBAY_SOLD_TO_AUTOMATICALLY_AUTHORIZE_PURCHASES = 'southbay_sold_to_automatically_authorize_purchases';

    public function getSegmentation();
    public function setSegmentation($value);

    public function getId();

    public function setId($id);

    public function getCountryCode();

    public function setCountryCode($value);

    public function getSapCountryCode();

    public function setSapCountryCode($sapCountryCode);

    public function getChannelCode();

    public function setChannelCode($channelCode);

    public function getSectionCode();

    public function setSectionCode($sectionCode);

    public function getCustomerCode();

    public function setCustomerCode($customerCode);

    public function getOldCustomerCode();

    public function setOldCustomerCode($oldCustomerCode);

    public function getCountryBusinessCode();

    public function setCountryBusinessCode($countryBusinessCode);

    public function getCustomerName();

    public function setCustomerName($customerName);

    public function getLocked();

    public function setLocked($locked);

    public function getAutomaticallyAuthorizePurchases();

    public function setAutomaticallyAuthorizePurchases($automaticallyAuthorizePurchases);

    /**
     * Get state
     *
     * @return bool
     */
    public function getIsInternal();

    /**
     * Set state
     *
     * @param bool $value
     * @return $this
     */
    public function setIsInternal($value);
}
