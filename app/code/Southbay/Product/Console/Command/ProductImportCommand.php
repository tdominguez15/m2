<?php

namespace Southbay\Product\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImportCommand extends Command
{
    protected function configure()
    {
        $this->setName('southbay:load:product')->setDescription('Load products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Psr\Log\LoggerInterface $log
         */
        $log = $objectManager->get('Psr\Log\LoggerInterface');
        $file = __DIR__ . '/productos.txt';

        $output->writeln('Reading file: ' . $file);

        $repository = $objectManager->get('Southbay\Product\Model\ResourceModel\SouthbaySapProduct');
        $factory = $objectManager->get('Southbay\Product\Model\SouthbaySapProductFactory');

        $this->read($file, $repository, $factory, $log, $output);

        $output->writeln('End');

        return 1;
    }

    private function read($file, $repository, $factory, $log, OutputInterface $output)
    {
        $resource = fopen($file, 'r');
        $total = 0;
        while ($line = fgets($resource)) {
            $total++;

            if ($total == 1) {
                continue;
            }

            $line = rtrim($line, "\r\n");
            $str = explode("\t", $line);

            $data = [];
            $data['southbay_catalog_product_country_code'] = 'AR';
            $data['southbay_catalog_product_sap_country_code'] = 'A01P';
            $data['southbay_catalog_product_sku'] = $str[0];
            $data['southbay_catalog_product_sku_generic'] = $str[2];
            $data['southbay_catalog_product_sku_variant'] = $str[3];
            $data['southbay_catalog_product_sku_full'] = $str[1];
            $data['southbay_catalog_product_name'] = $str[4];
            $data['southbay_catalog_product_color'] = $str[5];
            $data['southbay_catalog_product_size'] = $str[6];
            $data['southbay_catalog_product_ean'] = $str[13];
            $data['southbay_catalog_product_group_code'] = $str[7];
            $data['southbay_catalog_product_group_name'] = $str[8];
            $data['southbay_catalog_product_season_name'] = $str[11];
            $data['southbay_catalog_product_season_year'] = $str[12];
            $data['southbay_catalog_product_bu'] = $this->getBu($str[10]); // 10 - Ropa, 20 - Calzado, 30 - Accesorios
            $data['southbay_catalog_product_gender'] = null;
            $data['southbay_catalog_product_age'] = null;
            $data['southbay_catalog_product_sport'] = null;
            $data['southbay_catalog_product_shape_1'] = null;
            $data['southbay_catalog_product_shape_2'] = null;
            $data['southbay_catalog_product_brand'] = null;
            $data['southbay_catalog_product_channel'] = null;
            $data['southbay_catalog_product_level'] = null;
            $data['southbay_catalog_product_price'] = null;
            $data['southbay_catalog_product_suggested_retail_price'] = null;
            $data['southbay_catalog_product_denomination'] = null;
            $data['southbay_catalog_product_sale_date_from'] = date('Y-m-d',strtotime($str[15]));
            $data['southbay_catalog_product_sale_date_to'] = strtotime($str[16]);

            if (date('Y', $data['southbay_catalog_product_sale_date_to']) == '9999') {
                $data['southbay_catalog_product_sale_date_to'] = null;
            }

            $this->create($repository, $factory, $data, $log, $output);
        }

        fclose($resource);
    }

    /**
     * @param \Southbay\Product\Model\ResourceModel\SouthbaySapProduct $repository
     * @param \Southbay\Product\Model\SouthbaySapProductFactory $factory
     * @param mixed $data
     * @param \Psr\Log\LoggerInterface $log
     * @return void
     */
    private function create($repository, $factory, $data, $log, OutputInterface $output)
    {
        try {
            /**
             * @var \Southbay\Product\Model\SouthbaySapProduct $model
             */
            $model = $factory->create();
            $model->setData($data);
            $repository->save($model);
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            $log->error('Error creating product', ['data' => $data, 'error' => $e]);
        }
    }

    private function getBu($value)
    {
        if ($value == 'Calzado') {
            return '20';
        } else if ($value == 'Accesorios') {
            return '30';
        } else if ($value == 'Ropa') {
            return '10';
        }

        return null;
    }
}
