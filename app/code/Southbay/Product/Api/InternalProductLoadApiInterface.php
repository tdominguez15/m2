<?php

namespace Southbay\Product\Api;

use Southbay\Product\Api\Response\InternalProductLoadResponseInterface;

interface InternalProductLoadApiInterface
{
    /**
     * @param mixed $data
     * @return InternalProductLoadResponseInterface
     */
    public function save($data);
}
