<?php

namespace Southbay\CustomCheckout\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadSapOrders extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:download:sap-orders')
            ->setDescription('Download sap success order');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql = "SELECT * FROM southbay_sap_interface s WHERE s.southbay_sap_interface_from = 'futures' AND s.southbay_sap_interface_status = 'success' and created_at > '2024-07-15'";

        $data = [];
        $rows = $connection->fetchAll($sql);

        foreach ($rows as $row) {
            $order_id = $row['southbay_sap_interface_ref'];
            $doc_id = $row['southbay_sap_interface_id'];
            $request = json_decode($row['southbay_sap_interface_request'], true);
            $flow = $request['row']['REQ_DATE_H'];
            $samples = explode('.', $flow);
            $flow = $samples[2] . '-' . $samples[1] . '-' . $samples[0];

            $b2b = $request['row']['TEXTO'];
            $so = $request['row']['KUNNR_AG'];
            $dm = $request['row']['KUNNR_WE'];
            $items = $request['row']['ITEMS'];

            foreach ($items as $item) {
                $variant = $item['MATNR'];
                $qty = intval($item['KWMENG']);

                $data[] = [
                    $doc_id,
                    $order_id,
                    $b2b,
                    $so,
                    $dm,
                    $flow,
                    $variant,
                    $qty
                ];
            }
        }

        $fp = fopen('data-' . time() . '.csv', 'w');
        fputcsv($fp, ["doc id", "order id", "b2b", "so", "dm", "flow", "variant", "qty"], ";");

        foreach ($data as $line) {
            fputcsv($fp, $line, ";");
        }

        fclose($fp);
        return 1;
    }
}
