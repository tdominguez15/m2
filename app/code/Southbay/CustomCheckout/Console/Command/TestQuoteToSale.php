<?php

namespace Southbay\CustomCheckout\Console\Command;

use Magento\Quote\Model\QuoteRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestQuoteToSale extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:test:quote:to:sale')->addArgument('quote_id')->setDescription('Check quote to sale');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('quote_id');
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var QuoteRepository $repository
         */
        $repository = $objectManager->get(QuoteRepository::class);

        /**
         * @var \Southbay\Product\Helper\Data $southbay_helper
         */
        $southbay_helper = $objectManager->get(\Southbay\Product\Helper\Data::class);
        $quote = $repository->get($id);

        /**
         * @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $collection
         */
        /*
        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('quote_id', $id);
        $collection->load();
        */

        $items = $quote->getAllItems();

        $output->writeln($quote->getId() . ' ' . $quote->getCreatedAt() . '. total items: ' . count($items));
        $total_qty = 0;
        $total_qty_quote = 0;

        foreach ($items as $item) {
            $product = $item->getProduct();
            $config = $southbay_helper->getProductSeasonConfig($product);
            $totales = $southbay_helper->getTotalFromSeasonConfigBySize($product);

            $output->writeln("========================================");
            $output->writeln($product->getSku());
            $output->writeln('qty: ' . $item->getQty());
            $output->writeln(json_encode($config));
            $total_qty_quote += $item->getQty();
            $output->writeln("========================================");

            $parent = $southbay_helper->findParentProduct($product->getId());
            $children = $southbay_helper->getProductVariants($parent);

            $total_children = 0;

            /**
             * @var \Magento\Catalog\Model\Product $_product
             */
            foreach ($children as $_product) {
                $size = $_product->getSouthbaySize();

                $output->writeln('Intentando agregar/actualizar talle: ' . $size);

                if (!isset($totales[$size])) {
                    $output->writeln('No hay totales para el talle: ' . $size);
                } else if ($totales[$size]['general'] == 0) {
                    $output->writeln('El total es cero para el talle: ' . $size . '-' . $_product->getSku());
                } else {
                    $total_qty += $totales[$size]['general'];
                    $total_children += $totales[$size]['general'];
                }
            }

            $output->writeln("*********************************************");
            $output->writeln($parent->getSku() . ": " . $item->getQty() . '|' . $total_children);
            $output->writeln("*********************************************");
        }

        $output->writeln('Total: ' . $total_qty);
        $output->writeln('Total quote: ' . $total_qty_quote);

        return 1;
    }
}
