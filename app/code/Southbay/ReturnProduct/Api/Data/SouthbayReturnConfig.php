<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnConfig
{
    const TABLE = 'southbay_return_config';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = self::TABLE . '_id';
    const ENTITY_COUNTRY_CODE = 'southbay_return_country_code';
    const ENTITY_TYPE = 'southbay_return_type';
    const ENTITY_MAX_YEAR_HISTORY = 'southbay_return_max_year_history';
    const ENTITY_ORDER = 'southbay_return_order';
    const ENTITY_LABEL_TEXT = 'southbay_return_label_text';
    const ENTITY_AVAILABLE_AUTOMATIC_APPROVAL = 'southbay_return_available_automatic_approval';
    const ENTITY_MAX_AUTOMATIC_AMOUNT = 'southbay_return_max_automatic_amount';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();

    public function setCountryCode($value);

    public function getType();

    public function setType($value);

    public function getMaxYearHistory();

    public function setMaxYearHistory($value);

    public function getOrder();

    public function setOrder($value);

    public function getLabelText();

    public function setLabelText($value);

    public function getAvailableAutomaticApproval();

    public function setAvailableAutomaticApproval($value);

    public function getMaxAutomaticAmount();

    public function setMaxAutomaticAmount($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
