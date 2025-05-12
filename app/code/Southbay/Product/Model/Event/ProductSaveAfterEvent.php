<?php

namespace Southbay\Product\Model\Event;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Southbay\Product\Model\SouthbayProductChangesHistoryRepository;

class ProductSaveAfterEvent implements ObserverInterface
{
    private $log;
    private $changesHistoryRepository;

    public function __construct(\Psr\Log\LoggerInterface                $log,
                                SouthbayProductChangesHistoryRepository $changesHistoryRepository)
    {
        $this->log = $log;
        $this->changesHistoryRepository = $changesHistoryRepository;
    }

    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        $product = $observer->getEvent()->getProduct();

        $this->changesHistoryRepository->save($product);
    }
}
