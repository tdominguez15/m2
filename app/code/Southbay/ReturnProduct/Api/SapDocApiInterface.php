<?php

namespace Southbay\ReturnProduct\Api;

use Southbay\ReturnProduct\Api\Data\SapDocApiResponseInterface;

interface SapDocApiInterface
{
    /**
     * @param mixed $rows
     * @return SapDocApiResponseInterface
     */
    public function save($rows): SapDocApiResponseInterface;
}
