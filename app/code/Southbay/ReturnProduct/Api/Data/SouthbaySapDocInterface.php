<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbaySapDocInterface
{
    const STATUS_INIT = 'init';
    const TABLE = 'southbay_sap_doc_interface';
    const ENTITY_ID = 'entity_id';
    const TYPE = 'type';
    const STATUS = 'status';
    const RESULT_MSG = 'result_msg';
    // Indica a que hora comenzo la importación de los datos
    const START_IMPORT_DATE = 'start_import_date';
    // Indica a que hora finalizo la importación de los datos
    const END_IMPORT_DATE = 'end_import_date';
    const DATA = 'data';

    const RETRY_AT = 'retry_at';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function getId();

    public function setId($id);

    public function getType();

    public function setType($value);

    public function getStatus();

    public function setStatus($value);

    public function getResultMsg();

    public function setResultMsg($value);

    public function getStartImportDate();

    public function setStartImportDate($value);

    public function getEndImportDate();

    public function setEndImportDate($value);

    public function getRawData();

    public function setRawData($value);

    public function getRetryAt();

    public function setRetryAt($value);

    public function getCreatedAt();

    public function setCreatedAt($value);

    public function getUpdatedAt();

    public function setUpdatedAt($value);
}
