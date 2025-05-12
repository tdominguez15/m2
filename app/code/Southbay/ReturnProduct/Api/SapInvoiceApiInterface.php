<?php

namespace Southbay\ReturnProduct\Api;

use Southbay\ReturnProduct\Api\Data\SapDocApiResponseInterface;

interface SapInvoiceApiInterface
{
    /**
     * @param mixed $data
     * @return SapDocApiResponseInterface
     */
    public function save($data): SapDocApiResponseInterface;
}
