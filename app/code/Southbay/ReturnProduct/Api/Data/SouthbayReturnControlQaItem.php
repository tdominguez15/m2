<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnControlQaItem
{
    const TABLE = 'southbay_return_control_qa_item';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_RETURN_ID = 'southbay_return_id';
    const ENTITY_CONTROL_QA_ID = 'southbay_return_control_qa_id';
    const ENTITY_SKU = self::TABLE . '_sku';
    const ENTITY_SIZE = self::TABLE . '_size';
    const ENTITY_QTY_RETURN = self::TABLE . '_qty_return';
    const ENTITY_QTY_REAL = self::TABLE . '_qty_real';
    const ENTITY_QTY_EXTRA = self::TABLE . '_qty_extra';
    const ENTITY_QTY_MISSING = self::TABLE . '_qty_missing';
    const ENTITY_QTY_ACCEPTED = self::TABLE . '_qty_accepted';
    const ENTITY_QTY_REJECT = self::TABLE . '_qty_reject';
    const ENTITY_REJECT_REASON_CODES = self::TABLE . '_reason_codes';
    const ENTITY_REJECT_REASON_TEXT = self::TABLE . '_reject_reason_text';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function setReturnId($value);

    public function getReturnId();

    public function setSku($value);

    public function getSku();

    public function setSize($value);

    public function getSize();

    public function setControlQaId($value);

    public function getControlQaId();

    public function setQtyAccepted($value);

    public function getQtyAccepted();

    public function setQtyReal($value);

    public function getQtyReal();

    public function setQtyReturn($value);

    public function getQtyReturn();

    public function setQtyExtra($value);

    public function getQtyExtra();

    public function setQtyReject($value);

    public function getQtyReject();

    public function setQtyMissing($value);

    public function getQtyMissing();

    public function setReasonCodes($value);

    public function getReasonCodes();

    public function setReasonText($value);

    public function getReasonText();

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
