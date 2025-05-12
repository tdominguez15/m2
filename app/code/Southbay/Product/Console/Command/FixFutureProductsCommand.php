<?php

namespace Southbay\Product\Console\Command;

use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\Product\Cron\SouthbayProductImportCron;
use Southbay\Product\Model\Import\ProductLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixFutureProductsCommand extends Command
{
    protected function configure()
    {
        $this->setName('southbay:fix:future:products')
            ->setDescription('Fix future products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var MapCountryRepository $mapCountryRepository
         */
        $mapCountryRepository = $objectManager->get(MapCountryRepository::class);

        /**
         * @var ConfigStoreRepository $configStoreRepository
         */
        $configStoreRepository = $objectManager->get(ConfigStoreRepository::class);

        /**
         * @var StoreManagerInterface $storeManager
         */
        $storeManager = $objectManager->get(StoreManagerInterface::class);

        /**
         * @var SouthbayProductImportCron $productImportCron
         */
        $productImportCron = $objectManager->get(SouthbayProductImportCron::class);

        /**
         * @var ProductLoader $product_loader
         */
        $product_loader = $objectManager->get(ProductLoader::class);

        /**
         * @var \Southbay\Product\Helper\Data $productHelper
         */
        $productHelper = $objectManager->get(\Southbay\Product\Helper\Data::class);

        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();

        $items = $mapCountryRepository->getAll();
        $cache = [];

        foreach ($items as $item) {
            $config_store = $configStoreRepository->findStoreByFunctionCodeAndCountry(ConfigStoreInterface::FUNCTION_CODE_FUTURES, $item->getCountryCode());

            if (is_null($config_store)) {
                continue;
            }

            $store = $storeManager->getStore($config_store->getSouthbayStoreCode());


            // $store_code = $store->getCode();
            // $source_code = $productHelper->getSourceCodeByStoreCode($store_code);
            $website_id = $store->getWebsiteId();
            $stock_id = $productHelper->getStockIdByStore($store);

            /**
             * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $product_collection
             */
            $product_collection = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)->create();
            $product_collection->addFieldToFilter('type_id', ['in' => ['simple']]);
            $product_collection->setStoreId($store->getId());
            $product_collection->addWebsiteFilter([$website_id]);
            $product_collection->load();

            $total = $product_collection->getSize();
            $counter = 0;

            $output->writeln("Fixing future products for $total products...");

            $update_stock_request = [];

            foreach ($product_collection as $product) {
                $counter++;
                // $output->writeln("FIX SKU: " . $product->getSku() . '; store_id: ' . $store->getId() . ';progress: ' . $counter . '/' . $total);

                $sku = $product->getSku();

                if (isset($cache[$sku])) {
                    continue;
                }

                $product_id = $product->getId();
                $sql = "SELECT qty FROM cataloginventory_stock_status WHERE product_id = $product_id AND website_id in (0) AND stock_id = $stock_id";
                $stock = intval($connection->fetchOne($sql));
                $min_qty = 100000;
                $qty = 999999;

                if ($stock < $min_qty) {
                    // $product_loader->setSourceStock($product->getSku(), $store->getCode(), false, $item->getCountryCode());
                    // $product_loader->setSourceStockBySourceCode($product->getSku(), 'default', 999999);
                    // $product_loader->setSourceStockByStockId($product->getId(), $store->getWebsiteId(), 1, 999999);
                    $output->writeln("##SKU " . $sku . " is out of stock: " . $stock);
                    /*
                    $product_loader->setStock($product->getSku(), $qty, 'default', $store->getWebsiteId(), $store->getId(), $stock_id);
                    */

                    $update_stock_request[] = [
                        'type' => 'update',
                        'attr' => 'stock',
                        'sku' => $sku,
                        'source_code' => 'default',
                        'value' => $qty,
                        'store_id' => $config_store->getSouthbayStoreCode(),
                        'stock_id' => $stock_id,
                        'website_id' => $store->getWebsiteId(),
                        'at_once' => false,
                        'from_atp' => false,
                        'price' => null,
                        'country_code' => $item->getCountryCode()
                    ];

                    $cache[$sku] = $product_id;
                } else {
                    $cache[$sku] = $product_id;
                }
            }

            if (!empty($update_stock_request)) {
                $productImportCron->sendRequestFromMemory($update_stock_request, $config_store->getSouthbayStoreCode(), false);
            }
        }

        return 1;
    }
}
