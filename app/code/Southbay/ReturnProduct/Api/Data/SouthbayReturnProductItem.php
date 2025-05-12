<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnProductItem
{
    const TABLE = 'southbay_return_item';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'southbay_return_item_id';
    const ENTITY_RETURN_ID = 'southbay_return_id';
    const ENTITY_INVOICE_ID = 'southbay_invoice_id';
    const ENTITY_INVOICE_ITEM_ID = 'southbay_invoice_item_id';
    const ENTITY_SKU = 'southbay_return_item_sku';
    const ENTITY_SKU2 = 'southbay_return_item_sku2';
    const ENTITY_NAME = 'southbay_return_item_name';
    const ENTITY_SIZE = 'southbay_return_item_size';
    const ENTITY_REASONS_CODE = 'southbay_return_item_reasons_code';
    const ENTITY_REASONS_TEXT = 'southbay_return_item_reasons_text';
    const ENTITY_NET_UNIT_PRICE = 'southbay_return_item_net_unit_price';
    const ENTITY_NET_AMOUNT = 'southbay_return_item_net_amount';
    const ENTITY_QTY = 'southbay_return_item_qty';
    const ENTITY_STATUS = 'southbay_return_item_status';
    const ENTITY_STATUS_NAME = 'southbay_return_item_status_name';
    const ENTITY_QTY_ACCEPTED = 'southbay_return_qty_accepted';
    const ENTITY_AMOUNT_ACCEPTED = 'southbay_return_amount_accepted';
    const ENTITY_QTY_REJECTED = 'southbay_return_qty_rejected';
    const ENTITY_QTY_REAL = 'southbay_return_qty_real';
    const ENTITY_QTY_EXTRA = 'southbay_return_qty_extra';
    const ENTITY_QTY_MISSING = 'southbay_return_qty_missing';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function setReturnId($value);

    public function getReturnId();

    public function setInvoiceId($value);

    public function getInvoiceid();

    public function setInvoiceItemId($value);

    public function getInvoiceItemId();

    public function setReasonsCode($value);
    public function getReasonsCode();

    public function setReasonsText($value);
    public function getReasonsText();

    public function setSku($value);

    public function getSku();

    public function setSku2($value);

    public function getSku2();

    public function setName($value);

    public function getName();

    public function setSize($value);

    public function getSize();

    public function setNetUnitPrice($value);

    public function getNetUnitPrice();

    public function setNetAmount($value);

    public function getNetAmount();

    public function setQty($value);

    public function getQty();

    public function setStatus($value);

    public function getStatus();

    public function getStatusName();

    public function setStatusName($value);

    public function getQtyAccepted();

    public function setQtyAccepted($value);

    public function getAmountAccepted();

    public function setAmountAccepted($value);

    public function getQtyRejected();

    public function setQtyRejected($value);

    public function getQtyExtra();

    public function setQtyExtra($value);

    public function getQtyMissing();

    public function setQtyMissing($value);

    public function getQtyReal();

    public function setQtyReal($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
