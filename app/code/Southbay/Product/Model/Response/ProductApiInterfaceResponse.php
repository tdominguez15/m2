<?php

namespace Southbay\Product\Model\Response;

class ProductApiInterfaceResponse implements \Southbay\Product\Api\Response\ProductInterfaceResponse
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @param string $status
     * @param string $message
     */
    public function __construct($status, $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
