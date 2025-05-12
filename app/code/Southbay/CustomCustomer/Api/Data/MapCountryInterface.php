<?php

namespace Southbay\CustomCustomer\Api\Data;

/**
 * Interface MapCountryInterface
 * @package Southbay\MapCountry\Api\Data
 */
interface MapCountryInterface
{
    const TABLE = 'southbay_map_country';

    const FRONTERA_CHANNEL = '40';
    const FRONTERA_ORDER_ENTRY_DOC = 'Z018';

    /**
     * Constants for keys of data array.
     */
    const SOUTHBAY_MAP_COUNTRY_ID = 'southbay_map_country_id';
    const SOUTHBAY_MAP_COUNTRY_CODE = 'southbay_map_country_code';
    const SOUTHBAY_MAP_SAP_COUNTRY_CODE = 'southbay_map_sap_country_code';
    const SOUTHBAY_MAP_SAP_COUNTRY_CODE_FRONTERA = 'southbay_map_sap_country_code_frontera';

    const SOUTHBAY_MAP_SAP_CHANNEL = 'southbay_map_sap_country_channel';
    const SOUTHBAY_MAP_SAP_ZONE = 'southbay_map_sap_country_zone';
    const SOUTHBAY_MAP_SAP_FUTURE_DOC = 'southbay_map_sap_country_future_doc';
    const SOUTHBAY_MAP_SAP_AT_ONCE_DOC = 'southbay_map_sap_country_at_once_doc';

    const SOUTHBAY_MAP_STOCK_ID = 'southbay_map_stock_id';
    const SOUTHBAY_MAP_SAP_SOURCE_CODE = 'southbay_map_sap_source_code';
    const SOUTHBAY_MAP_SAP_WAREHOSE_CODE = 'southbay_map_sap_warehouse_code';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get country code.
     *
     * @return string|null
     */
    public function getCountryCode();

    /**
     * Set country code.
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode);

    /**
     * Get SAP country code.
     *
     * @return string|null
     */
    public function getSapCountryCode();

    /**
     * Set SAP country code.
     *
     * @param string $sapCountryCode
     * @return $this
     */
    public function setSapCountryCode($sapCountryCode);

    public function setSapCountryCodeFrontera($value);

    public function getSapCountryCodeFrontera();

    /**
     * Get creation time.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set creation time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update time.
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set update time.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    public function getStockId();

    public function setStockId($stockId);

    public function getSapStockId();

    public function setSapStockId($stockId);

    public function getSapWarehouseCode();

    public function setSapWarehouseCode($sapWarehouseCode);

    public function setSapChannel($value);

    public function getSapChannel();

    public function getSapZone();

    public function setSapZone($sapZone);

    public function getSapFutureDoc();

    public function setSapFutureDoc($sapFutureDoc);

    public function getSapAtOnceDoc();

    public function setSapAtOnceDoc($sapAtOnceDoc);
}
