<?php

namespace Southbay\Product\Api\Data;

interface ProductSapInterface
{
    const TABLE = 'southbay_season';
    const ENTITY_ID = 'season_id';
    const ENTITY_RAW_DATA = 'southbay_data';
    const ENTITY_STATUS = 'southbay_status';
    const ENTITY_RESULT_MSG = 'southbat_result_msg';
    const ENTITY_START_IMPORT_DATE = 'start_import_date';
    const ENTITY_END_IMPORT_DATE = 'end_import_date';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);

    public function getRawData();

    public function setRawData($value);

    public function getStatus();

    public function setStatus($value);

    public function getResultMsg();

    public function setResultMsg($value);

    public function setStartDate($value);

    public function getStartDate();

    public function setEndDate($value);

    public function getEndDate();
}
