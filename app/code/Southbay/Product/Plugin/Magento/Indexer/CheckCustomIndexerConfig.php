<?php

namespace Southbay\Product\Plugin\Magento\Indexer;

class CheckCustomIndexerConfig
{
    const SOUTHBAY_IMPORT_PRODUCTS_CACHE_KEY = 'southbay_import_products';
    const SOUTHBAY_UPDATE_PRODUCTS_CACHE_KEY = 'southbay_update_products';
    const MAGENTO_COMMAND_INDEXER = 'magento_command_indexer';
    const MAGENTO_CRON_INDEXER = 'magento_cron_indexer';

    private $cacheManager;

    public function __construct(\Magento\Framework\App\CacheInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function isRunning()
    {
        $cron_indexer = (bool)$this->cacheManager->load(self::MAGENTO_CRON_INDEXER);
        $command_indexer = (bool)$this->cacheManager->load(self::MAGENTO_COMMAND_INDEXER);

        return ($cron_indexer || $command_indexer);
    }

    public function checkIndexerActive()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $log = $objectManager->get('Psr\Log\LoggerInterface');

        $import_products = (bool)$this->cacheManager->load(self::SOUTHBAY_IMPORT_PRODUCTS_CACHE_KEY);
        $update_products = (bool)$this->cacheManager->load(self::SOUTHBAY_UPDATE_PRODUCTS_CACHE_KEY);

        if ($import_products || $update_products) {
            $log->debug('CheckCustomIndexerConfig. Index process stop', ['import_products' => $import_products, 'update_products' => $update_products]);
            return false;
        }

        return true;
    }

    public function startCronIndexer()
    {
        /**
         * max ttl 2 hours
         */
        $this->cacheManager->save(true, self::MAGENTO_CRON_INDEXER, [], 12000);
    }

    public function startCommandIndexer()
    {
        /**
         * max ttl 2 hours
         */
        $this->cacheManager->save(true, self::MAGENTO_COMMAND_INDEXER, [], 12000);
    }

    public function startImportProducts()
    {
        /**
         * max ttl 2 hours
         */
        $this->cacheManager->save(true, CheckCustomIndexerConfig::SOUTHBAY_IMPORT_PRODUCTS_CACHE_KEY, [], 12000);
    }

    public function startUpdateProducts()
    {
        /**
         * max ttl 1 hour
         */
        $this->cacheManager->save(true, CheckCustomIndexerConfig::SOUTHBAY_UPDATE_PRODUCTS_CACHE_KEY, [], 6000);
    }

    public function stopImportProducts()
    {
        $this->cacheManager->remove(CheckCustomIndexerConfig::SOUTHBAY_IMPORT_PRODUCTS_CACHE_KEY);
    }

    public function stopUpdateProducts()
    {
        $this->cacheManager->remove(CheckCustomIndexerConfig::SOUTHBAY_UPDATE_PRODUCTS_CACHE_KEY);
    }

    public function stopCronIndexer()
    {
        $this->cacheManager->remove(CheckCustomIndexerConfig::MAGENTO_CRON_INDEXER);
    }

    public function stopCommandIndexer()
    {
        $this->cacheManager->remove(CheckCustomIndexerConfig::MAGENTO_COMMAND_INDEXER);
    }
}
