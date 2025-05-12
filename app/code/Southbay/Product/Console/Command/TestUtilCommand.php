<?php

namespace Southbay\Product\Console\Command;

use Magento\Authorization\Model\RoleFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\State;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\Product\Cron\SouthbayProductImportCron;
use Southbay\Product\Cron\SouthbaySapProductImportCron;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestUtilCommand extends Command
{
    protected function configure()
    {
        $this->setName('southbay:test:util')
            ->setDescription('Test utils');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $this->runCron();
        // $this->testUpdatePrice();
        // $this->testUpdateProduct();
        // $this->getStock($output);
        // $this->resizeImg();
        // $this->disableMagentoStock($output);
        // $this->testLoadExcel($output);
        // $this->testCheckProductsATP($output);
        //$this->getProductPrice($output);
        // $this->getAdminUsers($output);
        // $this->checkProductSalable($output);
        $this->stopIndex($output);
        // $this->startIndex($output);

        return 1;
    }

    private function stopIndex(OutputInterface $output)
    {
        $output->writeln('Stopping indexer...');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
         */
        $indexerRegistry = $objectManager->get('Magento\Framework\Indexer\IndexerRegistry');

        /**
         * @var \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
         */
        $indexerCollectionFactory = $objectManager->get('Magento\Indexer\Model\Indexer\CollectionFactory');
        $collection = $indexerCollectionFactory->create();
        $collection->load();
        $ids = $collection->getAllIds();

        foreach ($ids as $id) {
            $model = $indexerRegistry->get($id);
            $model->setScheduled(false);
            $model->save();

            $output->writeln('index: ' . $model->getTitle() . '. status: ' . $model->getStatus());
        }
    }

    private function startIndex(OutputInterface $output)
    {


    }

    private function checkProductSalable(OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $factory = $objectManager->get(ProductCollectionFactory::class);
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
         */
        $collection = $factory->create();
        $collection->addIdFilter([14071, 14072]);
        $collection->setStore(4);
        $items = $collection->getItems();

        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        foreach ($items as $product) {
            $output->writeln(sprintf('<info>%s</info>', $product->getStore()->getId()));
            $output->writeln('sku: ' . $product->getSku() . '. ' . ($product->isSalable() ? 'y' : 'n') . '. ' . ($product->getOptionsContainer() == 'container1' ? 'container1' : 'container2'));
            $output->writeln('sku: ' . $product->getSku(), 'a:' . $product->isAvailable() ? 'y' : 'n');
            $o = $product->getTypeInstance()->hasOptions($product);
            $output->writeln('o: ' . ($o ? 'yes' : 'no'));
        }
    }

    private function getAdminUsers(OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $roleFactory = $objectManager->get(RoleFactory::class);
        $userCollectionFactory = $objectManager->get(\Magento\User\Model\ResourceModel\User\CollectionFactory::class);
        $userCollection = $userCollectionFactory->create();
        // $userCollection->addFieldToFilter('is_active', 1);

        foreach ($userCollection as $user) {
            $roles = $user->getRoles();
            // $output->writeln(sprintf('<info>%s</info> c: %s', $user->getEmail(), count($roles)));

            foreach ($roles as $roleId) {
                $role = $roleFactory->create()->load($roleId);
                $roleName = $role->getRoleName() ?? 'Unknown';
                $output->writeln(sprintf(
                    "%s;%s;%s;%s",
                    $user->getUsername(),
                    $roleName,
                    $roleId,
                    $user->getIsActive()
                ));
            }
        }
    }

    private function getProductPrice(OutputInterface $output)
    {
        $sku = 'DD9294-001';

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var State $state
         */
        $state = $objectManager->get(State::class);
        $state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        /**
         * @var StoreManagerInterface $storeManager
         */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore(5);

        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);

        /**
         * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
         */
        $configurableType = $objectManager->get(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class);
        $associatedProducts = $configurableType->getUsedProducts($product);
        $prices = [];
        foreach ($associatedProducts as $simpleProduct) {
            $prices[] = $simpleProduct->getFinalPrice();
            $output->writeln("- Sku: " . $simpleProduct->getSku() . ". Price: " . $simpleProduct->getPrice());
        }
        $minPrice = min($prices);

        $priceCurrency = $objectManager->get(PriceCurrencyInterface::class);
        $formattedPrice = $priceCurrency->format($minPrice, true);
        $minPrice2 = $product->getMinimalPrice();
        $minPrice2 = $priceCurrency->format($minPrice2, true);

        $output->writeln("<info>Frontend price for {$sku}: {$formattedPrice}</info>");
        $output->writeln("<info>Frontend price for {$sku}: {$minPrice2}</info>");
    }

    private function testCheckProductsATP(OutputInterface $output)
    {
        $output->writeln('testCheckProductsATP...');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\Product\Model\ResourceModel\SouthbaySapProduct\CollectionFactory $factory
         */
        $factory = $objectManager->get('Southbay\Product\Model\ResourceModel\SouthbaySapProduct\CollectionFactory');
        $collection = $factory->create();
        $collection->addFieldToFilter('southbay_catalog_product_sku_generic', '10253916');
        $collection->addFieldToFilter('southbay_catalog_product_country_code', 'AR');
        $collection->load();

        $items = $collection->getItems();

        $list = [];

        /**
         * @var \Southbay\Product\Model\SouthbaySapProduct $sap_product
         */
        foreach ($items as $sap_product) {
            $columns = [];

            $columns['generic'] = $sap_product->getSkuGeneric();
            $columns['variant'] = $sap_product->getSkuVariant();
            $columns['sku_full'] = !empty($sap_product->getSkuFull()) ? $sap_product->getSkuFull() : $sap_product->getSku() . '/' . $sap_product->getSize();
            $columns['sku'] = $sap_product->getSku();
            $columns['ean'] = $sap_product->getEan();
            $columns['size'] = $sap_product->getSize();
            $columns['group'] = 'V01013705_01';
            $columns['season'] = $sap_product->getSeasonName();
            $columns['season_year'] = $sap_product->getSeasonYear();
            $columns['name'] = $sap_product->getName();
            $columns['color'] = $sap_product->getColor();
            $columns['segmentation'] = '';
            $columns['initiative'] = '';
            $columns['purchase_unit'] = 1;
            $columns['price_rt'] = 0;
            $columns['price_wh'] = $sap_product->getPrice();
            $columns['description'] = '';
            $list[] = $columns;
        }

        /**
         * @var SouthbayProductImportCron $productImportCron
         */
        $productImportCron = $objectManager->get(SouthbayProductImportCron::class);
        // $productImportCron->send_as_asyn = false;
        $productImportCron->loadFromMemory($list, 3, true);
    }

    private function testLoadExcel(OutputInterface $output)
    {
        $file = BP . '/var/test/TEST.xlsx';

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $lines = 0;
        $total_items = 0;
        $start_on_line_number = 3;

        foreach ($sheet->getRowIterator() as $row) {
            $lines++;
            if ($lines < $start_on_line_number) {
                continue;
            }

            $column_index = -1;
            $stop = false;

            $columns = [];

            foreach ($row->getCellIterator() as $cell) {
                $column_index++;
                $value = $cell->getValue();

                if (empty($value) && $column_index === 0) {
                    $stop = true;
                    break;
                }

                $value = trim(strval($value));

                switch ($column_index) {
                    case 2:
                    {
                        $columns['sku_full'] = $value;
                        break;
                    }
                    case 12:
                    {
                        // $columns['starte_date'] = $value;
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        $output->writeln('SKU: ' . $columns['sku_full'] . '. VALUE: ' . $value . '; VALUE DATE: ' . $date->format('Y-m-d H:i:s'));
                        break;
                    }
                }
            }

            if ($stop) {
                break;
            }
        }
    }

    private function disableMagentoStock(OutputInterface $output)
    {
        $output->writeln("<info>Disable Magento Stock:" . \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK . "</info>");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var Writer $writer
         */
        $writer = $objectManager->get(Writer::class);
        $writer->save(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK, 0, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 2);
        $output->writeln("Value saved");

        /**
         * @var ScopeConfigInterface $scopeConfig
         */
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $value = $scopeConfig->getValue(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 2);
        $output->writeln("New value: " . json_encode(['new_value' => $value]));
    }

    private function resizeImg()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $factory = $objectManager->get('Magento\Framework\Image\Factory');

        $source = BP . '/var/tmp/CZ5356-124-SPSLH000.png';
        $target = BP . '/var/tmp/CZ5356-124-SPSLH000-RESULT.png';

        $image = $factory->create($source);

        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor([255, 255, 255]);

        $image->resize(150, 150);

        $image->save($target);
    }

    private function getStock(OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $stockRegistry
         */
        $stockRegistry = $objectManager->get('Magento\InventoryApi\Api\GetSourceItemsBySkuInterface');
        $items = $stockRegistry->execute('553558-132/9.5');

        $output->writeln(sprintf('<info>%s</info>', count($items)));

        foreach ($items as $item) {
            $output->writeln(sprintf('<info>%s</info>', $item->getSourceCode()));
            $output->writeln(sprintf('<info>%s</info>', $item->getSku()));
            $output->writeln(sprintf('<info>%s</info>', $item->getQuantity()));
        }
    }

    private function testUpdateProduct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\Product\Model\Import\ProductManager $manager
         */
        $manager = $objectManager->get(\Southbay\Product\Model\Import\ProductManager::class);
        $product = $manager->findProduct('553558-132/9.5', 3);

        try {
            $objectManager->get(State::class)->setAreaCode('adminhtml');
        } catch (\Exception $e) {
        }

        $objectManager->get(\Magento\Catalog\Model\ProductRepository::class)->save($product);
    }


    private function testUpdatePrice()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var SouthbaySapProductImportCron $cron
         */
        $cron = $objectManager->get(SouthbaySapProductImportCron::class);

        $factory = $objectManager->get('Southbay\Product\Model\SouthbaySapProductFactory');

        /**
         * @var \Southbay\Product\Model\SouthbaySapProduct $sapProduct
         */
        $sapProduct = $factory->create();
        $sapProduct->setPrice(10000);
        $sapProduct->setCountryCode('AR');
        $sapProduct->setSku('397690-169');
        $sapProduct->setSkuGeneric('10270221');

        $cron->updateMagentoProduct($sapProduct);

        /**
         * @var \Southbay\Product\Model\SouthbaySapProduct $sapProduct
         */
        $sapProduct = $factory->create();
        $sapProduct->setPrice(10000);
        $sapProduct->setCountryCode('AR');
        $sapProduct->setSku('397690-169');
        $sapProduct->setSkuGeneric('10270221');

        $cron->updateMagentoProduct($sapProduct);
    }

    private function runCron()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var SouthbayProductImportCron $cron
         */
        $cron = $objectManager->get(SouthbayProductImportCron::class);
        $cron->run();
    }
}
