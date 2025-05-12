<?php

namespace Southbay\ReturnProduct\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportInvoicesCommand extends Command
{
    protected function configure()
    {
        $this->setName('southbay:invoice:import')->setDescription('Import return product');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $file = __DIR__ . '/facturas/facturado alamo enero a junio 2022.txt';

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Psr\Log\LoggerInterface $log
         */
        $log = $objectManager->get('Psr\Log\LoggerInterface');

        /**
         * @var \Magento\Framework\MessageQueue\PublisherInterface $publisher
         */
        $publisher = $objectManager->get('Magento\Framework\MessageQueue\PublisherInterface');

        $output->writeln('Reading file: ' . $file);

        $this->read($file, $publisher, $log, $output);

        return 1;
    }

    private function read($file, $publisher, $log, $output)
    {
        $resource = fopen($file, 'r');

        $total = 0;
        $map_invoice = [];

        while ($line = fgets($resource)) {
            $total++;
            if ($total < 2) {
                continue;
            }

            $line = trim($line);
            $line = rtrim($line, "\r\n");

            if (empty($line)) {
                break;
            }

            $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');

            $items = explode("\t", $line);
            $key = $items[6] . '-' . $items[0];

            if (!isset($map_invoice[$key])) {
                $map_invoice[$key] = [
                    'items' => [],
                    'invoice' => $items
                ];
            }

            $map_invoice[$key]['items'][] = $items;
        }

        fclose($resource);

        $output->writeln('total lines: ' . $total);
        $output->writeln('total invoices: ' . count($map_invoice));

        foreach ($map_invoice as $data) {
            $str = json_encode($data);
            if ($str === false) {
                $output->writeln('fallo uno');
                $log->debug('fallo:', ['data' => $data, 'error' => json_last_error(), 'msg' => json_last_error_msg()]);
            } else {
                $publisher->publish('southbay_return_product_invoice_import', $str);
            }
        }
    }


}
