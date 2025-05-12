<?php
namespace Southbay\Product\Api\Data;
interface ChannelInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'channel_id';

    const ENTITY_CODE = 'channel_code';

    const ENTITY_NAME = 'channel_name';

    const ENTITY_CREATED_AT = 'created_at';

    const ENTITY_UPDATED_AT = 'updated_at';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getChannelCode();

    public function setChannelCode($code);

    public function getChannelName();

    public function setChannelName($name);

    public function getCreatedAt();

    public function setCreatedAt($date);

    public function getUpdatedAt();

    public function setUpdatedAt($date);
}
