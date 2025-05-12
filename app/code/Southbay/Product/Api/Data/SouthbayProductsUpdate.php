<?php

namespace Southbay\Product\Api\Data;

interface SouthbayProductsUpdate
{
    const TABLE = 'southbay_products_update';
    const ENTITY_ID = 'entity_id';
    const ENTITY_SEASON_IMPORT_ID = 'season_import_id';

    const ENTITY_SKU = 'sku';

    const ENTITY_PRODUCT_ID = 'product_id';

    const ENTITY_CREATED_AT = 'created_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getSeasonImportId();

    public function setSeasonImportId($value);

    public function getSku();

    public function setSku($value);

    public function getProductId();

    public function setProductId($value);

    public function getCreatedAt();

    public function setCreatedAt($value);
}
