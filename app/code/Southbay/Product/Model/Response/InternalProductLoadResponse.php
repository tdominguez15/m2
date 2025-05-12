<?php

namespace Southbay\Product\Model\Response;

class InternalProductLoadResponse implements \Southbay\Product\Api\Response\InternalProductLoadResponseInterface
{
    private $data;
    private $status;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
