<?php

namespace Southbay\Product\Helper;

use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;

class MagentoProduct
{

    private $configStoreRepository;
    private $log;
    private $storeManager;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager,
                                LoggerInterface                            $log,
                                ConfigStoreRepository                      $configStoreRepository)
    {
        $this->configStoreRepository = $configStoreRepository;
        $this->storeManager = $storeManager;
        $this->log = $log;
    }

    public function aroundGetSkipSaleableCheck(
        \Magento\Catalog\Helper\Product $subject,
        callable                        $proceed)
    {
        $this->log->debug('Product helper Result...');

        $store = $this->storeManager->getStore();
        $config = $this->configStoreRepository->findByStoreId($store->getId());

        if (!$config) {
            return $proceed();
        }

        $this->log->debug('Product helper Result', ['s' => $store->getId(), 'config' => $config->getSouthbayFunctionCode()]);

        if ($config->getSouthbayFunctionCode() == ConfigStoreRepositoryInterface::SOUTHBAY_FUTURES) {
            return true;
        }

        return $proceed();
    }
}
