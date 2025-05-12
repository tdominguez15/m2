<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SapOrderConfirmationApiResponseInterface
{
    /**
     * @return \Southbay\ReturnProduct\Model\SapOrderConfirmationApiResult
     */
    public function getReturn(): \Southbay\ReturnProduct\Model\SapOrderConfirmationApiResult;

    /**
     * @param \Southbay\ReturnProduct\Model\SapOrderConfirmationApiResult $return
     * @return void
     */
    public function setReturn(\Southbay\ReturnProduct\Model\SapOrderConfirmationApiResult $return): void;
}
