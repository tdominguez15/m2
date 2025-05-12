<?php

namespace Southbay\Issues\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixAtOnceVisibility20241022 extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:fix:at_once:visibility:20241022')->setDescription('Fix-20241022: Correccion para productos simples que tienen visibilidad "Catalog,Search" y tiene que ser "None"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Aplicando fix...</info>');

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql_control = "SELECT COUNT(*) AS t
                        FROM catalog_product_entity_int
                        WHERE entity_id IN (
	                        SELECT entity_id FROM catalog_product_entity WHERE type_id = 'simple'
                        ) AND attribute_id = 99 AND VALUE <> 1";

        $total = $connection->fetchOne($sql_control);

        $output->writeln('<info>Total de productos para afectar: ' . $total . '</info>');

        // attribute_id = 99 # Es el atributo visibility
        $sql = "UPDATE catalog_product_entity_int SET VALUE = 1 WHERE entity_id IN (
	            SELECT entity_id FROM catalog_product_entity WHERE type_id = 'simple'
                ) AND attribute_id = 99 AND VALUE <> 1";

        $connection->query($sql);

        $total = $connection->fetchOne($sql_control);

        // $output->writeln('<info>Total de productos que siguen con el issue: ' . $total . '</info>');

        $output->writeln('<info>Fin aplicacion fix.</info>');

        return 1;
    }
}
