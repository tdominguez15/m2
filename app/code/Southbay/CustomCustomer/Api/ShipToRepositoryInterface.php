<?php
namespace Southbay\CustomCustomer\Api;

use Southbay\CustomCustomer\Api\Data\ShipToInterface;

/**
 * Interface ShipToRepositoryInterface
 * @package Southbay\ShipTo\Api
 */
interface ShipToRepositoryInterface
{
    /**
     * Get ship to by ID.
     *
     * @param int $id
     * @return \Southbay\CustomCustomer\Api\Data\ShipToInterface
     */
    public function getById($id);

    /**
     * Save ship to.
     *
     * @param \Southbay\CustomCustomer\Api\Data\ShipToInterface $shipTo
     * @return \Southbay\CustomCustomer\Api\Data\ShipToInterface
     */
    public function save(ShipToInterface $shipTo);

    /**
     * Delete ship to.
     *
     * @param \Southbay\CustomCustomer\Api\Data\ShipToInterface $shipTo
     * @return void
     */
    public function delete(ShipToInterface $shipTo);
}
