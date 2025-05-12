<?php

namespace Southbay\Product\Console\Command;

use Southbay\CustomCustomer\Api\Data\MapCountryInterface;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadSAPProducts extends Command
{
    protected function configure()
    {
        $this->setName('southbay:fix:sap:products')
            ->addArgument('path')
            ->setDescription('Fix variants products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $factory = $objectManager->get('Southbay\Product\Model\ProductSapInterfaceFactory');
        $repository = $objectManager->get('Southbay\Product\Model\ResourceModel\ProductSapInterface');

        $path_folder = $input->getArgument('path');
        $files = scandir($path_folder);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.log')) {
                continue;
            }
            $file_path = $path_folder . '/' . $file;

            try {
                // $output->writeln('Loading ' . $file);
                $this->read($file_path, $factory, $repository, $output);
                // $output->writeln('End loading ' . $file);
            } catch (\Exception $e) {
                $output->writeln('<error>file: ' . $file_path . '. msg: ' . $e->getMessage() . '</error>');
                throw $e;
            }
        }

        return 1;
    }

    private function read($file, $factory, $repository, OutputInterface $output)
    {
        $resource = fopen($file, 'r');
        $total = 0;
        while ($line = fgets($resource)) {
            $total++;
            if ($total == 18) {
                $line = trim($line);
                $data = json_decode($line, true);

                if ($data === false || is_null($data) || !isset($data['ET_ART_PRC'])) {
                    break;
                }

                $output->writeln('<info>' . $file . '</info>');

                /**
                 * @var \Southbay\Product\Model\ProductSapInterface $productSap
                 */
                $productSap = $factory->create();
                $productSap->setStatus('ok');
                $productSap->setResultMsg(__('Pendiente de incorporar'));
                $productSap->setRawData(json_encode($data['ET_ART_PRC']));

                $repository->save($productSap);
            }
        }
        fclose($resource);
    }
}
