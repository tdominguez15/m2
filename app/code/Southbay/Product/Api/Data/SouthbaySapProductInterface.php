<?php

namespace Southbay\Product\Api\Data;

interface SouthbaySapProductInterface
{
    const TABLE = 'southbay_catalog_product';
    const CACHE_TAG = self::TABLE;

    const ENTITY_ID = 'southbay_catalog_product_id';
    const ENTITY_COUNTRY_CODE = 'southbay_catalog_product_country_code';
    const ENTITY_SAP_COUNTRY_CODE = 'southbay_catalog_product_sap_country_code';
    const ENTITY_MAGENTO_PRODUCT_ID = 'southbay_catalog_product_magento_product_id';
    const ENTITY_SKU = 'southbay_catalog_product_sku';
    const ENTITY_SKU_GENERIC = 'southbay_catalog_product_sku_generic';
    const ENTITY_SKU_VARIANT = 'southbay_catalog_product_sku_variant';
    const ENTITY_SKU_FULL = 'southbay_catalog_product_sku_full';
    const ENTITY_NAME = 'southbay_catalog_product_name';
    const ENTITY_COLOR = 'southbay_catalog_product_color';
    const ENTITY_SIZE = 'southbay_catalog_product_size';
    const ENTITY_EAN = 'southbay_catalog_product_ean';
    const ENTITY_GROUP_CODE = 'southbay_catalog_product_group_code';
    const ENTITY_GROUP_NAME = 'southbay_catalog_product_group_name';
    const ENTITY_SEASON_NAME = 'southbay_catalog_product_season_name';
    const ENTITY_SEASON_YEAR = 'southbay_catalog_product_season_year';
    const ENTITY_BU = 'southbay_catalog_product_bu';
    const ENTITY_GENDER = 'southbay_catalog_product_gender';
    const ENTITY_AGE = 'southbay_catalog_product_age';
    const ENTITY_SPORT = 'southbay_catalog_product_sport';
    const ENTITY_SHAPE_1 = 'southbay_catalog_product_shape_1';
    const ENTITY_SHAPE_2 = 'southbay_catalog_product_shape_2';
    const ENTITY_BRAND = 'southbay_catalog_product_brand';
    const ENTITY_CHANNEL = 'southbay_catalog_product_channel';
    const ENTITY_LEVEL = 'southbay_catalog_product_level';
    const ENTITY_PRICE = 'southbay_catalog_product_price';
    const ENTITY_SUGGESTED_RETAIL_PRICE = 'southbay_catalog_product_suggested_retail_price';
    const ENTITY_DENOMINATION = 'southbay_catalog_product_denomination';
    const ENTITY_SALE_DATE_FROM = 'southbay_catalog_product_sale_date_from';
    const ENTITY_SALE_DATE_TO = 'southbay_catalog_product_sale_date_to';

    const ENTITY_CREATED_AT = 'created_at';

    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getId();

    public function setId($value);

    public function getCountryCode();

    public function setCountryCode($value);

    public function getSapCountryCode();

    public function setSapCountryCode($value);

    public function getMagentoProductId();

    public function setMagentoProductId($value);

    public function getSku();

    public function setSku($value);

    public function getSkuGeneric();

    public function setSkuGeneric($value);

    public function getSkuVariant();

    public function setSkuVariant($value);

    public function getSkuFull();

    public function setSkuFull($value);

    public function getName();

    public function setName($value);

    public function getColor();

    public function setColor($value);

    public function getSize();

    public function setSize($value);

    public function getEan();

    public function setEan($value);

    public function getGroupCode();

    public function setGroupCode($value);

    public function getGroupName();

    public function setGroupName($value);

    public function getSeasonName();

    public function setSeasonName($value);

    public function getSeasonYear();

    public function setSeasonYear($value);

    public function getBu();

    public function setBu($value);

    public function getGender();

    public function setGender($value);

    public function getAge();

    public function setAge($value);

    public function getSport();

    public function setSport($value);

    public function getShape1();

    public function setShape1($value);

    public function getShape2();

    public function setShape2($value);

    public function getBrand();

    public function setBrand($value);

    public function getChannel();

    public function setChannel($value);

    public function getLevel();

    public function setLevel($value);

    public function getPrice();

    public function setPrice($value);

    public function getSuggestedRetailPrice();

    public function setSuggestedRetailPrice($value);

    public function getDenomination();

    public function setDenomination($value);

    public function getSaleDateFrom();

    public function setSaleDateFrom($value);

    public function getSaleDateTo();

    public function setSaleDateTo($value);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
