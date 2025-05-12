<?php

namespace Southbay\Product\Api\Data;

interface SouthbayProductImportHistoryInterface
{
    const TABLE = 'southbay_import_products_history';
    const ENTITY_ID = 'season_import_id';
    const ENTITY_FILE = 'file';
    const ENTITY_SEASON_ID = 'season_id';
    const ENTITY_STORE_ID = 'store_id';
    const ENTITY_IS_AT_ONCE = 'is_at_once';
    const ENTITY_STATUS = 'status';
    const ENTITY_RESULT_MSG = 'result_msg';
    const ENTITY_START_IMPORT_DATE = 'start_import_date';
    const ENTITY_END_IMPORT_DATE = 'end_import_date';
    const ENTITY_LINES = 'lines';
    const ENTITY_SKUS = 'skus';
    const ENTITY_TYPE = 'type';
    const ENTITY_ATTRIBUTE_CODE = 'attribute_code';
    const ENTITY_START_ON_LINE_NUMBER = 'start_on_line_number';
    const ENTITY_TYPE_OPERATION = 'type_operation';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    const STATUS_INIT = 'init';
    const STATUS_START = 'start';
    const STATUS_END = 'end';
    const STATUS_ERROR = 'error';

    const TYPE_IMPORT = 'import';
    const TYPE_UPDATE = 'update';

    public function getEntityId();

    public function setEntityId($entityId);

    public function setFile($name);

    public function getFile();

    public function getSeasonId();

    public function setSeasonId($seasonId);

    public function getStatus();

    public function setStatus($status);

    public function getResultMsg();

    public function setResultMsg($resultMsg);

    public function getStartImportDate();

    public function setStartImportDate($startImportDate);

    public function getEndImportDate();

    public function setEndImportDate($endImportDate);

    public function getLines();

    public function setLines($lines);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);

    public function getSkus();

    public function setSkus($skus);

    public function getStoreId();

    public function setStoreId($storeId);

    public function getIsAtOnce();

    public function setIsAtOnce($isAtOnce);

    public function getType();

    public function setType($value);

    public function getAttributeCode();

    public function setAttributeCode($value);

    public function getTypeOperation();

    public function setTypeOperation($value);
}
