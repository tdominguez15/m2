<?php

namespace Southbay\Product\Api;

use Southbay\Product\Api\Response\ProductInterfaceResponse;

interface ProductSapApiInterface
{
    /**
     * @param mixed $ET_ART_PRC
     * @return ProductInterfaceResponse
     */
    public function save($ET_ART_PRC);
}
