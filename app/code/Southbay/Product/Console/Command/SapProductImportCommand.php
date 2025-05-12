<?php

namespace Southbay\Product\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SapProductImportCommand extends Command
{
    protected function configure()
    {
        $this->setName('southbay:load:product:sap')->setDescription('Load products');
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
        $path = __DIR__ . '/sap_products';

        $output->writeln('Reading path: ' . $path);

        $repository = $objectManager->get('Southbay\Product\Model\ResourceModel\SouthbaySapProduct');
        $factory = $objectManager->get('Southbay\Product\Model\SouthbaySapProductFactory');

        $this->readDir($path, $repository, $factory, $log, $output);

        $output->writeln('End');

        return 1;
    }

    private function readDir($path, $repository, $factory, $log, OutputInterface $output)
    {
        $files = scandir($path, SCANDIR_SORT_ASCENDING);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $output->writeln($file);
            $this->read($path . '/' . $file, $repository, $factory, $log, $output);
        }
    }

    private function read($file, $repository, $factory, $log, OutputInterface $output)
    {
        $resource = fopen($file, 'r');
        $total = 0;
        while ($line = fgets($resource)) {
            $total++;

            if ($total <= 17) {
                continue;
            }

            $line = rtrim($line, "\r\n");
            $this->readData($file, $line, $repository, $factory, $log, $output);

            break;
        }

        fclose($resource);
    }

    private function readData($file, $str, $repository, $factory, $log, OutputInterface $output)
    {
        try {
            $total_items = 0;
            $data = json_decode($str, true);

            if (!empty($data)) {
                foreach ($data as $et_art_prc) {
                    if (!empty($et_art_prc['item'])) {
                        if (isset($et_art_prc['item']['LOCNR'])) {
                            try {
                                $item = $et_art_prc['item'];
                                if ($item['PRODUCT_RANKING'] != 'SERVICIOS') {
                                    $this->readItem($item, $repository, $factory, $log, $output);
                                    $total_items++;
                                }
                            } catch (\Exception $e) {
                                $output->writeln('Error reading item: [' . $e->getMessage() . ']');
                                $log->error('Error reading item', ['file' => $file, 'item' => $item, 'error' => $e]);
                            }
                        } else {
                            foreach ($et_art_prc['item'] as $item) {
                                try {
                                    if ($item['PRODUCT_RANKING'] != 'SERVICIOS') {
                                        $this->readItem($item, $repository, $factory, $log, $output);
                                        $total_items++;
                                    }
                                } catch (\Exception $e) {
                                    $output->writeln('Error reading item: [' . $e->getMessage() . ']');
                                    $log->error('Error reading item', ['file' => $file, 'item' => $item, 'error' => $e]);
                                }
                            }
                        }
                    }
                }
                $output->writeln('total items:' . $total_items);
            }
        } catch (\Exception $e) {
            $output->writeln('Error reading data: ' . $e->getMessage());
            $log->error('Error reading data', ['error' => $e]);
        }
    }

    private function readItem($item, $repository, $factory, $log, OutputInterface $output)
    {
        $data = [];
        $data['southbay_catalog_product_country_code'] = ($item['LOCNR'] == 'AP01' || $item['LOCNR'] == 'A01P' ? 'AR' : 'UY');
        $data['southbay_catalog_product_sap_country_code'] = ($item['LOCNR'] == 'AP01' || $item['LOCNR'] == 'A01P' ? 'A01P' : 'B01P');
        $data['southbay_catalog_product_sku'] = $item['IDNLF'];
        $data['southbay_catalog_product_sku_generic'] = substr(ltrim($item['MATNR'], '0'), 0, -3);
        $data['southbay_catalog_product_sku_variant'] = ltrim($item['MATNR'], '0');
        $data['southbay_catalog_product_sku_full'] = null;
        $data['southbay_catalog_product_name'] = $item['MAKTM'];
        $data['southbay_catalog_product_color'] = $item['WRF_COLOR_ATWTB'];
        $data['southbay_catalog_product_size'] = $item['WRF_SIZE1_ATWTB'];
        $data['southbay_catalog_product_ean'] = $item['EAN11'] ?? null;
        $data['southbay_catalog_product_group_code'] = null;
        $data['southbay_catalog_product_group_name'] = null;
        $data['southbay_catalog_product_season_name'] = $item['LABOR'] ?? null;
        $data['southbay_catalog_product_season_year'] = $item['FORMT'] ?? null;
        $data['southbay_catalog_product_bu'] = $this->getBu($item['CATEGORYCODE']);
        $data['southbay_catalog_product_gender'] = $item['GENDER'] ?? null;
        $data['southbay_catalog_product_age'] = $item['MERCH_DEPARTMENT'] ?? null;
        $data['southbay_catalog_product_sport'] = $item['WGBEZ'] ?? null;
        $data['southbay_catalog_product_shape_1'] = $item['PRODUCT_RANKING'] ?? null;
        $data['southbay_catalog_product_shape_2'] = null;
        $data['southbay_catalog_product_brand'] = null;
        $data['southbay_catalog_product_channel'] = null;
        $data['southbay_catalog_product_level'] = null;
        $data['southbay_catalog_product_price'] = $item['KWERT'] ?? null;
        $data['southbay_catalog_product_suggested_retail_price'] = null;
        $data['southbay_catalog_product_denomination'] = null;
        if (isset($item['ZZFECHA_INI_TEMP'])) {
            $date = \DateTime::createFromFormat('dmY', $item['ZZFECHA_INI_TEMP']);
            $data['southbay_catalog_product_sale_date_from'] = $date->format('Y-m-d');
        } else {
            $data['southbay_catalog_product_sale_date_from'] = null;
        }
        $data['southbay_catalog_product_sale_date_to'] = null;

        $this->create($repository, $factory, $data, $log, $output);
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
            if ($e->getMessage() != 'Product already exists.') {
                $output->writeln('Error: ' . $e->getMessage());
                $log->error('Error creating product', ['data' => $data, 'error' => $e]);
            }
        }
    }

    private function getBu($value)
    {
        $value = trim(strtolower($value));

        if ($value == 'calzado') {
            return '20';
        } else if ($value == 'accesorios') {
            return '30';
        } else if ($value == 'ropa') {
            return '10';
        }

        return null;
    }
}
