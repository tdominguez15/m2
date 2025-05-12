<?php

namespace Southbay\Product\Api\Data;

interface SouthbayProductGroupInterface
{
    const TABLE = 'southbay_product_group';

    //                       department
    const TYPE_DEPARTMENT = 'department';
    const TYPE_GENDER = 'gender';
    const TYPE_AGE = 'age';
    const TYPE_SPORT = 'sport';
    const TYPE_SHAPE_1 = 'silueta_1';
    const TYPE_SHAPE_2 = 'silueta_2';

    const ENTITY_ID = 'entity_id';
    const ENTITY_TYPE = 'type';
    const ENTITY_CODE = 'code';
    const ENTITY_NAME = 'name';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getType();

    public function setType($type);

    public function getCode();

    public function setCode($code);

    public function getName();

    public function setName($name);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
