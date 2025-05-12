<?php

namespace Southbay\Product\Plugin\Magento\Indexer\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomIndexerReindexCommand extends \Magento\Indexer\Console\Command\IndexerReindexCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig $checkCustomIndexerConfig
         */
        $checkCustomIndexerConfig = $objectManager->get('Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig');

        try {
            if ($checkCustomIndexerConfig->checkIndexerActive()) {
                $checkCustomIndexerConfig->startCommandIndexer();
                return parent::execute($input, $output);
            } else {
                return 0;
            }
        } finally {
            $checkCustomIndexerConfig->stopCommandIndexer();
        }
    }
}
