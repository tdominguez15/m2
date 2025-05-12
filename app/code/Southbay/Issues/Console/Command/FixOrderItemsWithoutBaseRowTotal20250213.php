<?php
namespace Southbay\Issues\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixOrderItemsWithoutBaseRowTotal20250213 extends Command
{
    protected $start_from = '2025-02-01';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:fix:future:orders:20250213')
            ->setDescription('Fix-20250213: correccion cantida de unidades ordenadas');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Apply fix " . $this->getName());

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql = "SELECT DISTINCT o.entity_id as order_id
                FROM sales_order o
                INNER JOIN sales_order_item i ON o.entity_id = i.order_id
                WHERE o.created_at >= '$this->start_from'
                AND i.qty_ordered =0";

        $order_ids = $connection->fetchAll($sql);

        foreach ($order_ids as $field) {
            $order_id = $field['order_id'];

            $collectionFactory = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
            /**
             * @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection
             */
            $collection = $collectionFactory->create();
            $collection->addFieldToFilter('entity_id', $order_id);
            $collection->load();

            if ($collection->getSize() == 0) {
                $output->writeln("Order #$order_id not found");
                continue;
            }

            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $order = $collection->getFirstItem();
            $items = $order->getItems();
            $to_fix = [];
            $info = [];

            foreach ($items as $item) {
                $product = $item->getProduct();
                $option = $item->getData('product_options');

                if ($item->getBaseRowTotal() == 0) {
                    $to_fix[] = $item;
                }

                if (isset($option['info_buyRequest']['month_delivery_date_1']) ||
                    isset($option['info_buyRequest']['month_delivery_date_2']) ||
                    isset($option['info_buyRequest']['month_delivery_date_3'])
                ) {
                    $sku = $item->getSku();
                    $sku = explode('/', $sku)[0];
                    $total_general = [];

                    $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_1'] ?? null, $total_general);
                    $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_2'] ?? null, $total_general);
                    $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_3'] ?? null, $total_general);

                    $info[$sku] = $total_general;

                    $output->writeln(
                        '#' . $order->getIncrementId() .
                        ' . SKU: ' . $sku .
                        ' . OPTION ID: ' . $product->getData('southbay_size') .
                        'info: ' . json_encode($info[$sku])
                    );
                }
            }

            foreach ($to_fix as $item) {
                $sku = $item->getSku();
                $sku = explode('/', $sku)[0];
                $product = $item->getProduct();
                $size = $product->getData('southbay_size');

                if (!isset($info[$sku])) {
                    $output->writeln(
                        '#' . $order->getIncrementId() .
                        ' . SKU: ' . $sku .
                        '. Not total general'
                    );
                    continue;
                }

                if (!isset($info[$sku][$size])) {
                    $output->writeln(
                        '#' . $order->getIncrementId() .
                        ' . SKU: ' . $sku .
                        ' . SKU Full: ' . $item->getSku() .
                        ' . OPTION ID: ' . $size .
                        '. Option without info'
                    );
                    continue;
                }

                $total = $info[$sku][$size];
                $output->writeln(
                    '#' . $order->getIncrementId() .
                    ' . SKU: ' . $product->getSku() .
                    ' . OPTION ID: ' . $product->getData('southbay_size') .
                    '. New qty: ' . $total
                );

                $item->setQtyOrdered($total);
                $item->setRowTotal($total * $item->getPrice());
                $item->setBaseRowTotal($item->getRowTotal());
                $item->setRowTotalInclTax($item->getRowTotal());
                $item->setBaseRowTotalInclTax($item->getRowTotal());

                $item->save();

                $order->setTotalQtyOrdered($order->getTotalQtyOrdered() + $total);
                $order->save();
            }
        }

        $output->writeln("End");

        return 1;
    }

    private function sumTotalGeneral($info_buyRequest, &$total_general)
    {
        if (!$info_buyRequest) {
            return;
        }

        foreach ($info_buyRequest as $key => $value) {
            if (!isset($total_general[$key])) {
                $total_general[$key] = $value;
            } else {
                $total_general[$key] += $value;
            }
        }
    }
}
