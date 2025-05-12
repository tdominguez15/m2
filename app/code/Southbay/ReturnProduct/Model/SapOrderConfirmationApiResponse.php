<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SapOrderConfirmationApiResponseInterface;

class SapOrderConfirmationApiResponse implements SapOrderConfirmationApiResponseInterface
{
    private $result;

    /**
     * @return SapOrderConfirmationApiResult
     */
    public function getReturn(): \Southbay\ReturnProduct\Model\SapOrderConfirmationApiResult
    {
        return $this->result;
    }


    /**
     * @param SapOrderConfirmationApiResult $return
     * @return void
     */
    public function setReturn(\Southbay\ReturnProduct\Model\SapOrderConfirmationApiResult $return): void
    {
        $this->result = $return;
    }
}
