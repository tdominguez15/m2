<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SapDocApiResponseInterface
{
    /**
     * @return \Southbay\ReturnProduct\Model\SapDocApiResult
     */
    public function getReturn(): \Southbay\ReturnProduct\Model\SapDocApiResult;

    /**
     * @param \Southbay\ReturnProduct\Model\SapDocApiResult $return
     * @return void
     */
    public function setReturn(\Southbay\ReturnProduct\Model\SapDocApiResult $return): void;
}
