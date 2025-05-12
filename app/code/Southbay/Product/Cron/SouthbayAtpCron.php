<?php

namespace Southbay\Product\Cron;

use Southbay\Product\Model\StockAtp;

class SouthbayAtpCron
{
    private $stockAtp;

    public function __construct(StockAtp $stockAtp)
    {
        $this->stockAtp = $stockAtp;
    }

    public function run()
    {
        $this->stockAtp->updateStock();
    }
}
