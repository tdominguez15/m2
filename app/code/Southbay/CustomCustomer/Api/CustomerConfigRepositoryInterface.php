<?php

namespace Southbay\CustomCustomer\Api;

use Southbay\CustomCustomer\Api\Data\ShipToInterface;

/**
 * Interface ShipToRepositoryInterface
 * @package Southbay\ShipTo\Api
 */
interface CustomerConfigRepositoryInterface
{
    /**
     * @param $email
     * @return \Southbay\CustomCustomer\Api\Data\CustomerConfigInterface|null
     */
    public function findByCustomerEmail($email);

    /**
     * Get ship to by ID.
     *
     * @param int $id
     * @return \Southbay\CustomCustomer\Api\Data\CustomerConfigInterface|null
     */
    public function getById($id);

    /**
     * Save ship to.
     *
     * @param mixed $data
     * @return \Southbay\CustomCustomer\Api\Data\CustomerConfigInterface
     */
    public function save($data);

    /**
     * Delete ship to.
     *
     * @param \Southbay\CustomCustomer\Api\Data\CustomerConfigInterface $field
     * @return void
     */
    public function delete($field);
}
