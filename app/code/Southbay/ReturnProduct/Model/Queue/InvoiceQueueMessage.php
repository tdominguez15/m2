<?php

namespace Southbay\ReturnProduct\Model\Queue;

class InvoiceQueueMessage
{
    private $invoice;

    private $items;

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setInvoice($value): void
    {
        $this->invoice = $value;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setItems($value): void
    {
        $this->items = $value;
    }
}
