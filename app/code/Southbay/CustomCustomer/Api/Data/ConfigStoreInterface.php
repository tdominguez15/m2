<?php

namespace Southbay\CustomCustomer\Api\Data;

/**
 * Interface ConfigStoreInterface
 * @api
 */
interface ConfigStoreInterface
{
    const FUNCTION_CODE_RTV = 'rtv';
    const FUNCTION_CODE_AT_ONCE = 'at_once';
    const FUNCTION_CODE_FUTURES = 'futures';

    const SOUTHBAY_GENERAL_CONFIG_ID = 'southbay_general_config_id';
    const SOUTHBAY_FUNCTION_CODE = 'southbay_function_code';
    const SOUTHBAY_COUNTRY_CODE = 'southbay_country_code';
    const SOUTHBAY_STORE_CODE = 'southbay_store_code';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get Southbay General Config ID
     *
     * @return int
     */
    public function getSouthbayGeneralConfigId();

    /**
     * Set Southbay General Config ID
     *
     * @param int $id
     * @return $this
     */
    public function setSouthbayGeneralConfigId($id);

    /**
     * Get Southbay Function Code
     *
     * @return string
     */
    public function getSouthbayFunctionCode();

    /**
     * Set Southbay Function Code
     *
     * @param string $functionCode
     * @return $this
     */
    public function setSouthbayFunctionCode($functionCode);

    /**
     * Get Southbay Country Code
     *
     * @return string
     */
    public function getSouthbayCountryCode();

    /**
     * Set Southbay Country Code
     *
     * @param string $countryCode
     * @return $this
     */
    public function setSouthbayCountryCode($countryCode);

    /**
     * Get Southbay Store Code
     *
     * @return int
     */
    public function getSouthbayStoreCode();

    /**
     * Set Southbay Store Code
     *
     * @param int $storeCode
     * @return $this
     */
    public function setSouthbayStoreCode($storeCode);

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
