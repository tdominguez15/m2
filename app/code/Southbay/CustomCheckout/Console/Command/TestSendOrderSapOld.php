<?php

namespace Southbay\CustomCheckout\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestSendOrderSapOld extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:test:send:order')
            ->addArgument('id', null, 'magento order id')
            ->addOption('zone', null, InputOption::VALUE_OPTIONAL, 'sap country zone')
            ->addOption('channel', null, InputOption::VALUE_OPTIONAL, 'sap channel')
            ->addOption('doc', null, InputOption::VALUE_OPTIONAL, 'sap doc')
            ->setDescription('Clean all products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->get(\Southbay\ApproveOrders\Cron\SendOrdersToSap::class)->sendAtOnce();

        return 1;
    }

    protected function executeOLD(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $id = $input->getArgument('id');
        $channel = $input->getOption('channel');
        $zone = $input->getOption('zone');
        $doc = $input->getOption('doc');

        /**
         * @var \Southbay\CustomCheckout\Model\SapInterface\SapOrderEntryFutureNotification $sender
         */
        $sender = $objectManager->get('Southbay\CustomCheckout\Model\SapInterface\SapOrderEntryFutureNotification');
        $sender->sendByIncrementOrder($id, $zone, $channel, $doc);

        return 1;
    }

    protected function executeRrr(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'southbay_variant' and entity_type_id = 4";
        $southbay_variant = $connection->fetchOne($sql);

        $sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'name' and entity_type_id = 4";
        $southbay_name = $connection->fetchOne($sql);

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
            $items = $request['row']['ITEMS'];

            foreach ($items as $item) {
                $variant = $item['MATNR'];
                $qty = intval($item['KWMENG']);

                $sql = "select entity_id from catalog_product_entity_varchar v where v.attribute_id = $southbay_variant and v.value = '$variant' and v.store_id = $id";
                $entity_id = $connection->fetchOne($sql);

                $sql = "select value from catalog_product_entity_varchar v where v.attribute_id = $southbay_name and v.entity_id = '$entity_id' and v.store_id = 0";
                $name = $connection->fetchOne($sql);


                $sql = "select sku from catalog_product_entity where entity_id = '$entity_id'";
                $sku = $connection->fetchOne($sql);


                if (!$sku) {
                    $output->writeln("<error>SKU $sku does not exist</error>");
                    $output->writeln($southbay_variant);
                    $output->writeln($variant);
                    $output->writeln($entity_id);
                }

                $samples = explode('/', $sku);
                $sku = $samples[0];
                $size = $samples[1];

                $data[] = [
                    'doc_id' => $doc_id,
                    'order_id' => $order_id,
                    'b2b' => $b2b,
                    'flow' => $flow,
                    'variant' => $variant,
                    'qty' => $qty,
                    'size' => $size,
                    'name' => $name,
                    'sku' => $sku
                ];
            }
        }

        file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));
        return 1;
    }

    protected function executeReport(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\CustomCheckout\Model\Report\SouthbayFutureOrderEntry $report
         */
        $report = $objectManager->get('Southbay\CustomCheckout\Model\Report\SouthbayFutureOrderEntry');
        $report->generate([
            'from' => '2024-05-01',
            'to' => '2025-05-28'
        ]);

        return 1;
    }

    protected function execute22(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Magento\Sales\Api\OrderRepositoryInterface $repository
         */
        $repository = $objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $order = $repository->get(25);

        /**
         * @var \Southbay\ReturnProduct\Helper\SendSapRequest $helper
         */
        $helper = $objectManager->get(\Southbay\ReturnProduct\Helper\SendSapRequest::class);

        if (!is_null($order)) {
            $output->writeln('Se encontro la orden: ');

            /**
             * @var \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterfaceConfigRepository $sapInterfaceConfigRepository
             */
            $sapInterfaceConfigRepository = $objectManager->get(\Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterfaceConfigRepository::class);
            $config = $sapInterfaceConfigRepository->getConfigByType(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::TYPE_PURCHASE_ORDER);

            $model_1 = $helper->sapOrderRequest([
                'id' => $order->getId(),
                'month' => 7,
                'year' => 2025
            ]);

            $model_2 = $helper->sapOrderRequest([
                'id' => $order->getId(),
                'month' => 8,
                'year' => 2025
            ]);

            $model_3 = $helper->sapOrderRequest([
                'id' => $order->getId(),
                'month' => 9,
                'year' => 2025
            ]);

            $helper->sendSapRequest($config, [
                ['model' => $model_1],
                ['model' => $model_2],
                ['model' => $model_3]
            ]);
        } else {
            $output->writeln('NO existe la orden');
        }

        return 1;
    }
}
