<?php

namespace Southbay\Product\Api\Data;

interface SouthbayProductImportImgHistoryInterface
{
    const TABLE = 'southbay_import_img_history';
    const ENTITY_ID = 'entity_id';
    const ENTITY_NAME = 'name';
    const ENTITY_FILE = 'file';
    const ENTITY_STATUS = 'status';
    const ENTITY_RESULT_MSG = 'result_msg';
    const ENTITY_START_IMPORT_DATE = 'start_import_date';
    const ENTITY_END_IMPORT_DATE = 'end_import_date';
    const ENTITY_TOTAL_FILES = 'total_files';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    const STATUS_INIT = 'init';
    const STATUS_START = 'start';
    const STATUS_END = 'end';
    const STATUS_ERROR = 'error';

    public function getEntityId();

    public function setEntityId($entityId);

    public function setName($name);

    public function getName();

    public function setFile($name);

    public function getFile();

    public function getStatus();

    public function setStatus($status);

    public function getResultMsg();

    public function setResultMsg($resultMsg);

    public function getStartImportDate();

    public function setStartImportDate($startImportDate);

    public function getEndImportDate();

    public function setEndImportDate($endImportDate);

    public function getTotalFiles();

    public function setTotalFiles($total);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
