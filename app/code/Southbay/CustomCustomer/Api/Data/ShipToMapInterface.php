<?php

namespace Southbay\CustomCustomer\Api\Data;

interface ShipToMapInterface
{
    const TABLE = 'southbay_ship_to_map';
    const ENTITY_ID = 'entity_id';
    const SOLD_TO_CODE = 'sold_to_code';
    const SOLD_TO_OLD_CODE = 'sold_to_old_code';
    const SHIP_TO_CODE = 'ship_to_code';
    const SHIP_TO_OLD_CODE = 'ship_to_old_code';

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

    public function setShipToCode($shipToCode);

    public function getShipToCode();

    public function setShipToOldCode($shipToOldCode);

    public function getShipToOldCode();
}
