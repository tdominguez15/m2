<?php

namespace Southbay\CustomCustomer\Api\Data;

interface SoldToMapInterface
{
    const TABLE = 'southbay_sold_to_map';
    const ENTITY_ID = 'entity_id';
    const SOLD_TO_CODE = 'sold_to_code';
    const SOLD_TO_OLD_CODE = 'sold_to_old_code';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    public function setSoldToCode($soldToCode);

    public function getSoldToCode();

    public function setSoldToOldCode($soldToOldCode);

    public function getSoldToOldCode();
}
