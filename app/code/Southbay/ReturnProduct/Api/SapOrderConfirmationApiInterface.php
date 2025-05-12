<?php

namespace Southbay\ReturnProduct\Api;

use Southbay\ReturnProduct\Api\Data\SapOrderConfirmationApiResponseInterface;

interface SapOrderConfirmationApiInterface
{
    /**
     * @param mixed $rows
     * @return SapOrderConfirmationApiResponseInterface
     */
    public function save($rows): SapOrderConfirmationApiResponseInterface;
}
