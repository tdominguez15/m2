<?php

namespace Southbay\Product\Api\Data;
interface SeasonTypeInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'type_id';

    const ENTITY_CODE = 'type_code';

    const ENTITY_NAME = 'type_name';

    const ENTITY_CREATED_AT = 'created_at';

    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getSeasonTypeCode();

    public function setSeasonTypeCode($code);

    public function getSeasonTypeName();

    public function setSeasonTypeName($name);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
