<?php

namespace Southbay\Product\Api\Response;

interface ProductInterfaceResponse
{
    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getMessage();
}
