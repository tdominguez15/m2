<?php

namespace Southbay\Product\Plugin\Magento\Indexer\Cron;

use Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig;

class CustomReindexAllInvalid extends \Magento\Indexer\Cron\ReindexAllInvalid
{
    private $checkCustomIndexerConfig;

    public function __construct(\Magento\Indexer\Model\Processor $processor, CheckCustomIndexerConfig $checkCustomIndexerConfig)
    {
        $this->checkCustomIndexerConfig = $checkCustomIndexerConfig;
        parent::__construct($processor);
    }

    public function execute()
    {
        try {
            if ($this->checkCustomIndexerConfig->checkIndexerActive()) {
                $this->checkCustomIndexerConfig->startCronIndexer();
                parent::execute();
            }
        } finally {
            $this->checkCustomIndexerConfig->stopCronIndexer();
        }
    }
}
