<?php

namespace Southbay\Product\Api\Data;
interface SeasonInterface
{
    const TABLE = 'southbay_season';
    const ENTITY_ID = 'season_id';
    const ENTITY_CODE = 'season_code';
    const ENTITY_NAME = 'season_name';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';
    const ENTITY_SEASON_TYPE_CODE = 'season_type_code';
    const ENTITY_SEASON_CATEGORY_ID = 'season_category_id';
    const ENTITY_SEASON_DESCRIPTION = 'season_description';

    const ENTITY_SEASON_START_AT = 'season_start_at';
    const ENTITY_SEASON_END_AT = 'season_end_at';

    const ENTITY_SEASON_COUNTRY_CODE = 'season_country_code';
    const ENTITY_SEASON_STORE_ID = 'store_id';
    const ENTITY_SEASON_ACTIVE = 'active';

    // <removed version="1.1.0">
    // const ENTITY_SEASON_ENABLED = 'season_enabled';
    // const ENTITY_SEASON_START_LOAD_CATALOG_DATE = 'start_load_catalog_date';
    // const ENTITY_SEASON_END_LOAD_CATALOG_DATE = 'end_load_catalog_date';
    // const ENTITY_SEASON_PURCHASE_START_DATE = 'purchase_start_date';
    // const ENTITY_SEASON_PURCHASE_END_DATE = 'purchase_end_date';
    // </removed>

    const ENTITY_SEASON_MONTH_DELIVERY_DATE_1 = 'month_delivery_date_1';
    const ENTITY_SEASON_MONTH_DELIVERY_DATE_2 = 'month_delivery_date_2';
    const ENTITY_SEASON_MONTH_DELIVERY_DATE_3 = 'month_delivery_date_3';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getSeasonCode();

    public function setSeasonCode($code);

    public function getSeasonName();

    public function setSeasonName($name);

    public function getSeasonTypeCode();

    public function setSeasonTypeCode($seasonTypeCode);

    public function getSeasonCategoryId();

    public function setSeasonCategoryId($seasonCategoryId);

    public function getSeasonDescription();

    public function setSeasonDescription($seasonDescription);

    public function getMonthDeliveryDate1();

    public function setMonthDeliveryDate1($monthDeliveryDate1);

    public function getMonthDeliveryDate2();

    public function setMonthDeliveryDate2($monthDeliveryDate2);

    public function getMonthDeliveryDate3();

    public function setMonthDeliveryDate3($monthDeliveryDate3);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);

    public function getCountryCode();

    public function setCountryCode($value);

    public function getStoreId();

    public function setStoreId($value);

    public function getStartAt();

    public function setStartAt($value);

    public function getEndAt();

    public function setEndAt($value);

    public function setActive($value);
    public function getActive();
}
