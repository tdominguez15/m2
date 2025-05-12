<?php

namespace Southbay\Product\Console\Command;

use Composer\Console\Input\InputOption;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\Product\Helper\ProductData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportProductFromStoreCommand extends Command
{
    protected function configure()
    {
        $this->setName('southbay:export:product')
            ->addArgument('store_id', null, 'store id')
            ->addOption('sku', 'sku', InputOption::VALUE_OPTIONAL, 'sku')
            ->setDescription('Export products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store_id = $input->getArgument('store_id');
        $sku = $input->getOption('sku');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var StoreManagerInterface $storeManager
         */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore($store_id);
        $group = $store->getGroup();
        $catalog_root_id = $group->getRootCategoryId();
        $csvTarget = BP . '/var/tmp/skus_' . $store_id . '.csv';

        $helper = $objectManager->get(ProductData::class);
        $helper->listProductByStoreId($store_id, $catalog_root_id, true, $csvTarget, $sku);

        return 1;
    }

    protected function executeTT(InputInterface $input, OutputInterface $output)
    {
        $store_id = $input->getArgument('store_id');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var StoreManagerInterface $storeManager
         */
        $storeManager = $objectManager->get(StoreManagerInterface::class);

        /**
         * @var \Magento\Catalog\Model\ProductRepository $repository
         */
        $repository = $objectManager->get(\Magento\Catalog\Model\ProductRepository::class);

        /**
         * @var \Magento\InventorySalesApi\Api\AreProductsSalableInterface $is_salable_service
         */
        $is_salable_service = $objectManager->get(\Magento\InventorySalesApi\Api\AreProductsSalableInterface::class);

        /**
         * @var \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface $has_stock_service
         */
        $has_stock_service = $objectManager->get(\Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface::class);

        $configurableProductCollectionFactory = $objectManager->get('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory');
        $salableProcessor = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Collection\SalableProcessor');

        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $product_collection
         */
        $product_collection = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)->create();

        /**
         * @var \Magento\Catalog\Model\CategoryRepository $category_repository
         */
        $category_repository = $objectManager->get(\Magento\Catalog\Model\CategoryRepository::class);

        $store = $storeManager->getStore($store_id);
        $group = $store->getGroup();

        $catalog_root_id = $group->getRootCategoryId();
        $category = $category_repository->get($catalog_root_id);
        $subCategoryIds = $category->getChildrenCategories()->getAllIds();


        $repository = $objectManager->get('Magento\Catalog\Model\ProductRepository');

        $product_collection->addCategoriesFilter(['in' => $subCategoryIds]);
        $product_collection->addFieldToFilter('type_id', ['in' => ['configurable', 'simple']]);
        $product_collection->addFieldToFilter('sku', ['in' => ['HV6937-702', 'SX7678-010']]);
        $product_collection->setStoreId($store_id);
        $product_collection->setOrder('sku', 'ASC');
        $product_collection->load(false, true);

        $output->writeln('Total de productos: ' . $product_collection->getSize());

        $price_list = $product_collection->getAllAttributeValues('southbay_price_retail');
        $price_wh_list = $product_collection->getAllAttributeValues('price');

        $products = $product_collection->getItems();

        $total = 0;
        $total_simple = 0;
        $list = [];

        foreach ($products as $product) {
            $total++;
            $id = $product->getId();

            $price = $price_list[$id][$store_id] ?? $price_list[$id][0] ?? 0;
            $price_wh = $price_wh_list[$id][$store_id] ?? $price_wh_list[$id][0] ?? 0;

            if ($price_wh == 0 || $price == 0) {
                $output->writeln('[' . $total . '] Product ' . $product->getSku() . ' without price');
            }

            if ($product->getTypeId() == 'configurable') {
                $_product = $repository->get($product->getSku(), false, $store_id);
                if (!$_product->isSaleable()) {
                    if (!$_product->isAvailable()) {
                        $output->writeln($product->getSku() . ': not available');
                        $list[] = $product->getSku() . ';NOT AVAILABLE';
                        $this->why($product, $salableProcessor, $configurableProductCollectionFactory, $output);
                    } else {
                        $list[] = $product->getSku() . ';AVAILABLE';
                    }
                }
            } else {
                $total_simple++;
            }

            $product = $repository->get($product->getSku(), false, $store_id);

            $list[] = $product->getSku() . ';' . $price . ';' . $price_wh;
        }

        array_unshift($list, 'Total: ' . $total_simple);

        file_put_contents(BP . '/var/tmp/skus_' . $store_id . '.txt', implode(PHP_EOL, $list));

        return 1;
    }

    private function why($product, $salableProcessor, $configurableProductCollectionFactory, OutputInterface $output)
    {
        if (($product->getOrigData('status') != $product->getData('status'))) {
            $output->writeln('why: ' . $product->getSku() . '; diff status');
        } else if ($this->isStockStatusChanged($product)) {
            $output->writeln('why: ' . $product->getSku() . '; stock status changed');
        } else if ($product->hasData('salable') && !$product->getData('salable')) {
            $output->writeln('why: ' . $product->getSku() . '; data salable on false');
        } else {
            $type_instance = $product->getTypeInstance();
            $output->writeln('*** ' . $product->getSku() . '; type instance: ' . get_class($type_instance));
            $salable = $type_instance->isSalable($product);

            if (!$salable) {
                $output->writeln('why: ' . $product->getSku() . '; type salable on false');
                if (!$this->isSalable($type_instance, $product, $salableProcessor, $configurableProductCollectionFactory)) {
                    $output->writeln('why: ' . $product->getSku() . '; none linked products available');
                } else if ($product->getStatus() != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                    $output->writeln('why: ' . $product->getSku() . '; status is not equals to STATUS_ENABLED; ' . $product->getStatus());
                }
            }
        }
    }

    private function isStockStatusChanged($product)
    {
        $stockItem = null;
        $extendedAttributes = $product->getExtensionAttributes();
        if ($extendedAttributes !== null) {
            $stockItem = $extendedAttributes->getStockItem();
        }
        $stockData = $product->getStockData();
        return (
            (is_array($stockData))
            && array_key_exists('is_in_stock', $stockData)
            && (null !== $stockItem)
            && ($stockItem->getIsInStock() != $stockData['is_in_stock'])
        );
    }

    private function isSalable($type_instance, $product, $salableProcessor, $configurableProductCollectionFactory)
    {
        $storeId = $type_instance->getStoreFilter($product);
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        if ($storeId === null && $product->getStoreId()) {
            $storeId = $product->getStoreId();
        }

        $collection = $this->getLinkedProductCollection($type_instance, $product, $configurableProductCollectionFactory);
        $collection->addStoreFilter($storeId);
        $collection = $salableProcessor->process($collection);
        return 0 !== $collection->getSize();
    }

    public function getLinkedProductCollection($type_instance, $product, $configurableProductCollectionFactory)
    {
        $collection = $configurableProductCollectionFactory->create()->setFlag(
            'product_children',
            true
        )->setProductFilter(
            $product
        );
        if (null !== $type_instance->getStoreFilter($product)) {
            $collection->addStoreFilter($type_instance->getStoreFilter($product));
        }

        return $collection;
    }
}
