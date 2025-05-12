<?php

namespace Southbay\Product\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckStockStatus implements ObserverInterface
{

    public function execute(Observer $observer)
    {
       $status = $observer->getEvent()->getData('status');
       $status->setDisplayStatus(false);
    }
}
