<?php

namespace Southbay\Product\Console\Command;

use Southbay\Product\Model\StockAtp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunStockAtp extends Command
{
    protected function configure()
    {
        $this->setName('southbay:run:atp')
            ->setDescription('Run atp');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var StockAtp $stockAtp
         */
        $stockAtp = $objectManager->get(StockAtp::class);
        $stockAtp->updateStock();

        return 1;
    }
}
