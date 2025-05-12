<?php

namespace Southbay\Issues\Api\Data;

interface SouthbayUiTest
{
    const TABLE = 'southbay_ui_test';
    const ENTITY_ID = 'entity_id';
    const ENTITY_NAME = 'name';

    const ENTITY_DESCRIPTION = 'description';

    const ENTITY_CONTENT = 'content';

    const ENTITY_RESULT = 'result';

    const ENTITY_TOTAL_EXECUTION = 'total_execution';

    const ENTITY_CREATED_AT = 'created_at';

    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getName();

    public function setName($value);

    public function getDescription();

    public function setDescription($value);

    public function getContent();

    public function setContent($value);

    public function getResult();

    public function setResult($value);

    public function getTotalExecution();

    public function setTotalExecution($value);

    public function getCreatedAt();

    public function setCreatedAt($value);

    public function getUpdatedAt();

    public function setUpdatedAt($value);
}
