<?php

namespace Southbay\Product\Api\Data;

interface SouthbayProductChangesHistory
{
    const TABLE = 'southbay_product_changes_history';

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const ENTITY_PRODUCT_ID = 'product_id';
    const ENTITY_STORE_ID = 'store_id';
    const ENTITY_JSON_DATA = 'json';
    const ENTITY_HASH = 'hash';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getProductId();

    public function setProductId($productId);

    public function getStoreId();

    public function setStoreId($storeId);

    public function getHash();

    public function setHash($hash);

    public function getJsonData();

    public function setJsonData($data);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
