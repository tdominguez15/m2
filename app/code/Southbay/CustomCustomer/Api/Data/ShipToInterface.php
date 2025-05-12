<?php
namespace Southbay\CustomCustomer\Api\Data;

/**
 * Interface ShipToInterface
 * @package Southbay\ShipTo\Api\Data
 */
interface ShipToInterface
{
    const TABLE = 'southbay_ship_to';
    const SOUTHBAY_SHIP_TO_ID = 'southbay_ship_to_id';
    const SOUTHBAY_SHIP_TO_NAME = 'southbay_ship_to_name';
    const SOUTHBAY_SHIP_TO_CUSTOMER_CODE = 'southbay_ship_to_customer_code';
    const SOUTHBAY_SHIP_TO_CODE = 'southbay_ship_to_code';
    const SOUTHBAY_SHIP_TO_OLD_CODE = 'southbay_ship_to_old_code';
    const SOUTHBAY_SHIP_TO_ADDRESS = 'southbay_ship_to_address';
    const SOUTHBAY_SHIP_TO_ADDRESS_NUMBER = 'southbay_ship_to_address_number';
    const SOUTHBAY_SHIP_TO_STATE = 'southbay_ship_to_state';
    const SOUTHBAY_SHIP_TO_COUNTRY_CODE = 'southbay_ship_to_country_code';
    const SOUTHBAY_SHIP_TO_IS_INTERNAL = 'southbay_ship_to_is_internal';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    public function setName($value);
    public function getName();

    /**
     * Get customer code
     *
     * @return string|null
     */
    public function getCustomerCode();

    /**
     * Set customer code
     *
     * @param string $customerCode
     * @return $this
     */
    public function setCustomerCode($customerCode);

    /**
     * Get ship to code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set ship to code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get old ship to code
     *
     * @return string|null
     */
    public function getOldCode();

    /**
     * Set old ship to code
     *
     * @param string|null $oldCode
     * @return $this
     */
    public function setOldCode($oldCode);

    /**
     * Get address
     *
     * @return string|null
     */
    public function getAddress();

    /**
     * Set address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address);

    /**
     * Get address number
     *
     * @return string|null
     */
    public function getAddressNumber();

    /**
     * Set address number
     *
     * @param string $addressNumber
     * @return $this
     */
    public function setAddressNumber($addressNumber);

    /**
     * Get state
     *
     * @return string|null
     */
    public function getState();

    /**
     * Set state
     *
     * @param string $state
     * @return $this
     */
    public function setState($state);

    /**
     * Get country code
     *
     * @return string|null
     */
    public function getCountryCode();

    /**
     * Set country code
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode);

    /**
     * Get created at timestamp
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at timestamp
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at timestamp
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at timestamp
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

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
