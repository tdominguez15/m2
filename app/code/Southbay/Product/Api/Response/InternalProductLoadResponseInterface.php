<?php

namespace Southbay\Product\Api\Response;

interface InternalProductLoadResponseInterface
{
    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param mixed $data
     * @return void
     */
    public function setData($data);

    /**
     * @return mixed
     */
    public function getData();
}
