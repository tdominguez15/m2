<?php
namespace Southbay\CustomCustomer\Api;

use Southbay\CustomCustomer\Api\Data\SoldToInterface;

interface SoldToRepositoryInterface
{
    public function getById($id);

    public function save(SoldToInterface $soldTo);

    public function delete(SoldToInterface $soldTo);
}
