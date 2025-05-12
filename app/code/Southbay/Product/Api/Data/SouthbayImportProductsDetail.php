<?php

namespace Southbay\Product\Api\Data;

interface SouthbayImportProductsDetail
{
    const TABLE = 'southbay_import_products_detail';
    const ENTITY_ID = 'season_import_detail_id';
    const ENTITY_SEASON_ID = 'season_id';

    const ENTITY_SEASON_IMPORT_ID = 'season_import_id';

    const ENTITY_SKU = 'sku';

    const ENTITY_LINE = 'line';

    const ENTITY_STATUS = 'status';

    const ENTITY_RESULT_MSG = 'result_msg';

    const ENTITY_SOURCE_DATA = 'source_data';

    const ENTITY_PROCESS_DATA = 'process_data';

    const ENTITY_START_IMPORT_DATE = 'start_import_date';

    const ENTITY_END_IMPORT_DATE = 'end_import_date';

    const ENTITY_CREATED_AT = 'created_at';

    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getSeasonId();

    public function setSeasonId($value);

    public function getSeasonImportId();

    public function setSeasonImportId($value);

    public function getSku();

    public function setSku($value);

    public function getLine();

    public function setLine($value);

    public function getStatus();

    public function setStatus($value);

    public function getResultMsg();

    public function setResultMsg($value);

    public function getSourceData();

    public function setSourceData($value);

    public function getProcessData();

    public function setProcessData($value);

    public function getStartImportDate();

    public function setStartImportDate($value);

    public function getEndImportDate();

    public function setEndImportDate($value);

    public function getCreatedAt();

    public function setCreatedAt($value);

    public function getUpdatedAt();

    public function setUpdatedAt($value);
}
