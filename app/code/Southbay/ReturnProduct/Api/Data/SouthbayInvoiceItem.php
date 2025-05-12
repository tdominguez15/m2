<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayInvoiceItem
{
    const TABLE = 'southbay_invoice_item';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'southbay_invoice_item_id';
    const ENTITY_INVOICE_ID = 'southbay_invoice_id';
    const ENTITY_SKU = 'southbay_invoice_item_sku';
    const ENTITY_SKU2 = 'southbay_invoice_item_sku2';
    const ENTITY_SKU_GENERIC = 'southbay_invoice_item_sku_generic';
    const ENTITY_SKU_VARIANT = 'southbay_invoice_item_sku_variant';
    const ENTITY_BU = 'southbay_invoice_item_bu';

    const ENTITY_POSITION = 'southbay_invoice_item_position';

    const ENTITY_NAME = 'southbay_invoice_item_name';
    const ENTITY_SIZE = 'southbay_invoice_item_size';
    const ENTITY_QTY = 'southbay_invoice_item_qty';
    const ENTITY_AMOUNT = 'southbay_invoice_item_amount';
    const ENTITY_UNIT_PRICE = 'southbay_invoice_item_unit_price';
    const ENTITY_NET_UNIT_PRICE = 'southbay_invoice_net_item_unit_price';
    const ENTITY_NET_AMOUNT = 'southbay_invoice_item_net_amount';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function setId($value);

    public function getId();

    public function setInvoiceId($value);

    public function getInvoiceId();

    public function setSku($value);

    public function getSku();
    public function setSku2($value);

    public function getSku2();

    public function setSkuVariant($value);

    public function getSkuVariant();

    public function setSkuGeneric($value);

    public function getSkuGeneric();

    public function setBu($value);

    public function getBu();

    public function getPosition();
    public function setPosition($value);

    public function setName($value);

    public function getName();

    public function setSize($value);

    public function getSize();

    public function setQty($value);

    public function getQty();

    public function setAmount($value);

    public function getAmount();

    public function setUnitPrice($value);

    public function getUnitPrice();

    public function setNetUnitPrice($value);

    public function getNetUnitPrice();

    public function setNetAmount($value);

    public function getNetAmount();

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();
}
