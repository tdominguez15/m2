<?php

namespace Southbay\Product\Api\Data;

interface ProductExclusionInterface
{
    const TABLE = 'southbay_product_exclusion';
    const ENTITY_ID = 'entity_id';
    const ENTITY_SKU = 'sku';
    const ENTITY_STORE = 'store';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    /**
     * Get Entity ID
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set Entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get Store
     *
     * @return int
     */
    public function getStore();

    /**
     * Set Store
     *
     * @param int $store
     * @return $this
     */
    public function setStore($store);

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
