<?php

namespace Southbay\Product\Console\Command;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindProductBySku extends Command
{
    protected function configure()
    {
        $this->setName('southbay:find:product')
            ->addArgument('sku')
            ->setDescription('Find product by SKU');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sku = $input->getArgument('sku');

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var AttributeRepositoryInterface $interface
         */
        $interface = $objectManager->get(AttributeRepositoryInterface::class);
        $attr = $interface->get(Product::ENTITY, 'southbay_variant');

        $output->writeln('attr: ' . $attr->getAttributeId());

        /**
         * @var \Magento\Catalog\Model\ProductRepository $prepository
         */
        $prepository = $objectManager->create('Magento\Catalog\Model\ProductRepository');
        $product = $prepository->get('FN4446-001', false, 0);

        return 1;
    }
}
