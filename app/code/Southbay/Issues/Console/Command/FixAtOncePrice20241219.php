<?php

namespace Southbay\Issues\Console\Command;

use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\Product\Api\Data\SouthbayProduct;
use Southbay\Product\Model\Import\ProductManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixAtOncePrice20241219 extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:fix:atonce:price:20241219')->setDescription('Fix-20241219: Correccion en precios de los productos at once');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Aplicando fix...</info>');
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
         * @var ProductManager $product_manager
         */
        $product_manager = $objectManager->get(ProductManager::class);

        $sapProductCollectionFactory = $objectManager->get(\Southbay\Product\Model\ResourceModel\SouthbaySapProduct\CollectionFactory::class);

        $items = $mapCountryRepository->getAll();

        foreach ($items as $item) {
            $config_store = $configStoreRepository->findStoreByFunctionCodeAndCountry(ConfigStoreInterface::FUNCTION_CODE_AT_ONCE, $item->getCountryCode());

            if (is_null($config_store)) {
                continue;
            }

            $store = $storeManager->getStore($config_store->getSouthbayStoreCode());

            /**
             * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $product_collection
             */
            $product_collection = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)->create();
            $product_collection->addFieldToFilter('type_id', ['in' => ['simple']]);
            $product_collection->addAttributeToSelect(['price', SouthbayProduct::ENTITY_SKU_VARIANT]);
            $product_collection->setStoreId($store->getId());
            $product_collection->addWebsiteFilter([$store->getWebsiteId()]);
            $product_collection->load();
            $products = $product_collection->getItems();

            foreach ($products as $product) {
                $collection = $sapProductCollectionFactory->create();
                $collection->addFieldToFilter('southbay_catalog_product_sku_variant', $product->getData(SouthbayProduct::ENTITY_SKU_VARIANT));
                $collection->addFieldToFilter('southbay_catalog_product_country_code', $config_store->getSouthbayCountryCode());
                $collection->load();

                if ($collection->count() === 0) {
                    continue;
                }

                $_product = $collection->getFirstItem();

                if ($product->getData('price') && $product->getData('price') < $_product->getData('southbay_catalog_product_price')) {
                    $output->writeln('Updateding product. Store Id: ' . $store->getId() . '; ' . $product->getSku() . '; Old price: ' . $product->getData('price') . '; New price: ' . $_product->getData('southbay_catalog_product_price'));
                    $product_manager->updateAttrProduct($product->getSku(), $_product->getData('southbay_catalog_product_price'), SouthbayProduct::ENTITY_PRICE, $store->getId());
                }
            }
        }
        return 1;
    }
}
