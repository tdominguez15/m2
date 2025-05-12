<?php

namespace Southbay\Issues\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixOrderItemsWithoutAllVariants20250213 extends Command
{
    protected $start_from = '2025-02-01';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:fix:future:orders:20250213v2')
            ->setDescription('Fix-20250213v2: ordenes sin variantes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Apply fix " . $this->getName());

        /**
         *          * @var \Magento\Framework\App\ObjectManager $objectManager
         *                   */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         *          * @var \Southbay\Product\Helper\Data $helper
         *                   */
        $helper = $objectManager->get('Southbay\Product\Helper\Data');

        /**
         *          * @var \Magento\Sales\Model\Order\ItemFactory $quoteItemFactory
         *                   */
        $quoteItemFactory = $objectManager->get('Magento\Sales\Model\Order\ItemFactory');

        /**
         *          * @var \Magento\Framework\App\ResourceConnection $resource
         *                   */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql = "SELECT DISTINCT o.entity_id as order_id
									                    FROM sales_order o
											                    WHERE o.created_at >= '$this->start_from'
													                    and o.increment_id in ('2000000914', '2000000915', '2000000916', '2000000917', '2000000918')";

        $order_ids = $connection->fetchAll($sql);

        foreach ($order_ids as $field) {
            $order_id = $field['order_id'];

            $collectionFactory = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
            /**
             *              * @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection
             *                           */
            $collection = $collectionFactory->create();
            $collection->addFieldToFilter('entity_id', $order_id);
            $collection->load();

            if ($collection->getSize() == 0) {
                $output->writeln("Order #$order_id not found");
                continue;
            }

            /**
             *              * @var \Magento\Sales\Model\Order $order
             *                           */
            $order = $collection->getFirstItem();
            $items = $order->getItems();
            $to_fix = [];
            $info = [];
            $map_items = [];

            foreach ($items as $item) {
                $product = $item->getProduct();
                $option = $item->getData('product_options');
                $size = $product->getData('southbay_size');

                $sku = $item->getSku();
                $sku = explode('/', $sku)[0];

                if (!isset($map_items[$sku])) {
                    $map_items[$sku] = [];
                    $map_items[$sku]['default'] = $product->getId();
                }

                $map_items[$sku][$size] = $product->getId();

                if (isset($option['info_buyRequest']['month_delivery_date_1']) ||
                    isset($option['info_buyRequest']['month_delivery_date_2']) ||
                    isset($option['info_buyRequest']['month_delivery_date_3'])
                ) {
                    $total_general = [];

                    $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_1'] ?? null, $total_general);
                    $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_2'] ?? null, $total_general);
                    $this->sumTotalGeneral($option['info_buyRequest']['month_delivery_date_3'] ?? null, $total_general);

                    $info[$sku] = $total_general;
                }
            }

            foreach ($info as $sku => $total_general) {
                $keys = array_keys($total_general);

                foreach ($keys as $key) {
                    if (!isset($map_items[$sku][$key])) {
                        $to_fix[] = ['sku' => $sku, 'product_id' => $map_items[$sku]['default'], 'size' => $key, 'total' => $total_general[$key]];
                    }
                }
            }

            if (empty($to_fix)) {
                continue;
            }

            $output->writeln(
                '#' . $order->getIncrementId() . '. Total to fix: ' . count($to_fix)
            );

            $map_products = [];

            foreach ($to_fix as $fix) {
                $sku = $fix['sku'];
                if (!isset($map_products[$sku])) {
                    $parent = $helper->findParentProduct($fix['product_id']);
                    $parent->setStoreId($order->getStoreId());
                    $children = $helper->getProductVariants($parent);

                    $map_products[$sku] = [];

                    foreach ($children as $child) {
                        $size = $child->getData('southbay_size');
                        $map_products[$sku][$size] = $child;
                    }
                }

                if (!isset($map_products[$sku][$fix['size']])) {
                    $output->writeln(
                        '#' . $order->getIncrementId() . '. Size: ' . $fix['size'] . '. No available'
                    );
                    continue;
                }

                $_product = $map_products[$sku][$fix['size']];

                $_item = $quoteItemFactory->create();

                $_item->setProductType($_product->getTypeId());
                $_item->setProductId($_product->getId());

                $_item->setSku($_product->getSku());
                $_item->setName($_product->getName());

                $_item->setPriceInclTax($_product->getPrice());
                $_item->setBasePriceInclTax($_product->getPrice());

                $_item->setPrice($_product->getPrice());
                $_item->setBasePrice($_product->getPrice());

                $_item->setOriginalPrice($_product->getPrice());
                $_item->setBaseOriginalPrice($_product->getPrice());
                $_item->setStoreId($order->getStoreId());
                $_item->setQtyOrdered($fix['total']);

                $_item->setRowTotal($_item->getQtyOrdered() * $_item->getPrice());
                $_item->setBaseRowTotal($_item->getRowTotal());

                $_item->setRowTotalInclTax($_item->getRowTotal());
                $_item->setBaseRowTotalInclTax($_item->getRowTotal());

                $info = [
                    'form_key' => time(),
                    'qty' => 0,
                    'ignore' => true,
                    'product' => $_product->getId()
                ];
                $_item->setProductOptions([
                    'info_buyRequest' => $info
                ]);

                $order->addItem($_item);
                $order->setTotalQtyOrdered($order->getTotalQtyOrdered() + $_item->getQtyOrdered());
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
