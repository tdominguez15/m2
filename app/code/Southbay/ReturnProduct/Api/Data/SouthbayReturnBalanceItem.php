<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnBalanceItem
{
    const TABLE = 'southbay_return_balance';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_INVOICE_ID = self::TABLE . '_invoice_id';
    const ENTITY_INVOICE_ITEM_ID = self::TABLE . '_invoice_item_id';
    const ENTITY_TOTAL_INVOICED = self::TABLE . '_total_invoiced';
    const ENTITY_TOTAL_RETURN = self::TABLE . '_total_return';
    const ENTITY_TOTAL_AVAILABLE = self::TABLE . '_total_available';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getInvoiceId();

    public function setInvoiceId($value);

    public function getInvoiceItemId();

    public function setInvoiceItemId($value);

    public function getTotalInvoiced();

    public function setTotalInvoiced($value);

    public function getTotalReturn();

    public function setTotalReturn($value);

    public function getTotalAvailable();

    public function setTotalAvailable($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
