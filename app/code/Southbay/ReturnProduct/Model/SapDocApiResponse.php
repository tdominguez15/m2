<?php

namespace Southbay\ReturnProduct\Model;


// { "return": { "estado": “ERROR o OK", "mensaje": “ID Generado" } }
use Southbay\ReturnProduct\Api\Data\SapDocApiResponseInterface;

class SapDocApiResponse implements SapDocApiResponseInterface
{
    public SapDocApiResult $return;

    public function getReturn(): SapDocApiResult
    {
        return $this->return;
    }

    public function setReturn(SapDocApiResult $return): void
    {
        $this->return = $return;
    }
}
