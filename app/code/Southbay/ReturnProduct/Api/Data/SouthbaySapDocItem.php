<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbaySapDocItem
{
    const TABLE = 'southbay_sap_doc_item';
    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';

    const ENTITY_DOC_ID = 'southbay_sap_doc_id';
    const ENTITY_SKU = self::TABLE . '_sku';
    const ENTITY_QTY = self::TABLE . '_qty';
    const ENTITY_POSITION = self::TABLE . '_position';
    const ENTITY_NET_AMOUNT = self::TABLE . '_net_amount';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getDocId();
    public function setDocId($value);

    public function getSku();
    public function setSku($value);

    public function getQty();
    public function setQty($value);

    public function getPosition();
    public function setPosition($value);

    public function getNetAmount();
    public function setNetAmount($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
