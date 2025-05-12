<?php

namespace Southbay\Product\Api\Data;
interface OwnerGroupInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'owner_group_id';

    const ENTITY_COUNTRY_CODE = 'country_code';
    const ENTITY_CODE = 'owner_group_code';

    const ENTITY_NAME = 'owner_group_name';
    const ENTITY_SEGMENTATION = 'owner_group_segmentation';
    const ENTITY_TIER = 'owner_group_tier';

    const ENTITY_CREATED_AT = 'created_at';

    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getCountryCode();
    public function setCountryCode($country_code);

    public function getCode();

    public function setCode($code);

    public function getName();

    public function setName($name);

    public function getSegmentation();

    public function setSegmentation($segmentation);

    public function getTier();

    public function setTier($tier);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
