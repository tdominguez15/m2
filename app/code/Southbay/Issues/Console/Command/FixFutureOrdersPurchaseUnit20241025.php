<?php

namespace Southbay\Issues\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixFutureOrdersPurchaseUnit20241025 extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:fix:future:orders:20241025')->setDescription('Fix-20241025: Correccion multiplos de ordenes future');
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

        $from = '2024-08-01';

        $output->writeln('<info>Buscando ordenes future...</info>');
        $items = $this->getOrderItems($from, $connection);
        $output->writeln('<info>Total de items con purchase unit mayor que 1: ' . count($items) . '</info>');

        $items = $this->checkOrderWithDiff($items);

        $output->writeln('<info>Total de items con diferencias: ' . count($items) . '</info>');

        $items = $this->fixDiff($items, $output);

        $this->save($items, $connection, $output);

        $output->writeln('<info>Fin aplicacion fix.</info>');

        return 1;
    }

    private function save($items, $connection, OutputInterface $output)
    {
        $cache = [];

        foreach ($items as $item) {
            $_options = $item['options'];
            $_options_id = array_keys($_options);
            $options_size = [];
            $options = [];
            $order_id = $item['item']['order_id'];
            $parent_sku = explode('/', $item['item']['sku']);
            $parent_sku = $parent_sku[0];

            foreach ($_options_id as $_option_id) {
                if (!isset($cache[$_option_id])) {
                    $cache[$_option_id] = $this->getSizeLabel($_option_id, $connection);
                }

                $size = $cache[$_option_id];

                if (!empty($size)) {
                    $options_size[$_option_id] = $size;
                    $options[$_option_id] = $_options[$_option_id];
                } else {
                    $output->writeln('<error>Option ' . $_option_id . ' no encontrado</error>');
                }
            }

            foreach ($options as $option_id => $qty) {
                $sku = $parent_sku . '/' . $options_size[$option_id];

                $output->writeln('##Update qty. order id: ' . $order_id . '; sku: ' . $sku);

                $sql = "UPDATE sales_order_item SET
                        qty_ordered = $qty,
                        qty_shipped = CASE WHEN (qty_shipped > 0) THEN $qty ELSE 0 END,
                        row_total = (price * $qty),
                        base_row_total = (price * $qty),
                        row_total_incl_tax = (price * $qty),
                        base_row_total_incl_tax = (price * $qty)
                        WHERE order_id = $order_id AND sku = '$sku'";
                $connection->query($sql);
            }

            $sku = $item['item']['sku'];
            $output->writeln('#Update product options. order id: ' . $order_id . '; sku: ' . $sku);
            $json = json_encode(['info_buyRequest' => $item['data']]);
            $sql = "UPDATE sales_order_item SET product_options= '$json' WHERE order_id = $order_id AND sku = '$sku'";
            $connection->query($sql);
        }
    }

    private function getSizeLabel($option_id, $connection)
    {
        $sql = "SELECT value FROM eav_attribute_option_value WHERE option_id = $option_id";
        return $connection->fetchOne($sql);
    }

    private function fixDiff($items, OutputInterface $output)
    {
        $result = [];
        foreach ($items as $_item) {
            $item = $_item['item'];
            $data = $_item['data'];
            $total_qty = 0;
            $has_change = false;
            $options = [];
            foreach ($data as $key => $flow) {
                if ($key == 'month_delivery_date_1'
                    || $key == 'month_delivery_date_2'
                    || $key == 'month_delivery_date_3') {
                    foreach ($flow as $option_id => $qty) {
                        if ($qty > 0 && ($qty % $item['unit']) <> 0) {
                            $rest = $qty % $item['unit'];
                            $new_qty = $qty + abs($item['unit'] - $rest);
                            $output->writeln("#{$item['increment_id']}. SKU: {$item['sku']}, QTY: {$qty}, NEW QTY: {$new_qty}, UNIT: {$item['unit']}");
                            $flow[$option_id] = $new_qty;
                            $total_qty += $new_qty;
                            $has_change = true;
                            if (!isset($options[$option_id])) {
                                $options[$option_id] = 0;
                            }

                            $options[$option_id] += $new_qty;
                        } else {
                            $total_qty += $qty;
                        }
                    }
                    $data[$key] = $flow;
                }
            }
            if ($has_change) {
                $data['qty'] = $total_qty;
                $result[] = [
                    'item' => $item,
                    'data' => $data,
                    'options' => $options
                ];
            }
        }
        return $result;
    }

    private function checkOrderWithDiff($items)
    {
        $result = [];

        foreach ($items as $item) {
            $json = $item['product_options'];
            $data = json_decode($json, true);
            $data = $data['info_buyRequest'];

            $found = false;

            foreach ($data as $key => $flow) {
                if ($key == 'month_delivery_date_1'
                    || $key == 'month_delivery_date_2'
                    || $key == 'month_delivery_date_3') {
                    foreach ($flow as $option_id => $qty) {
                        if ($qty > 0 && ($qty % $item['unit']) <> 0) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        break;
                    }
                }
            }

            $result[] = [
                'item' => $item,
                'unit' => $item['unit'],
                'data' => $data
            ];
        }

        return $result;
    }

    private function getOrderItems($from, $connection)
    {
        $sql = "
			SELECT
			o.entity_id AS order_id,
			o.increment_id,
			o.store_id,
			item.sku,
			CASE WHEN (attr_store.value IS NOT NULL) THEN attr_store.value ELSE attr_default.value END AS unit,
			item.product_id,
			item.qty_ordered,
			item.product_options
			FROM sales_order o
			INNER JOIN sales_order_item item ON item.order_id = o.entity_id
			LEFT OUTER JOIN catalog_product_entity_int attr_store ON attr_store.entity_id = item.product_id AND attr_store.store_id = o.store_id AND attr_store.attribute_id = 169
			LEFT OUTER JOIN catalog_product_entity_int attr_default ON attr_default.entity_id = item.product_id AND attr_default.store_id = 0 AND attr_default.attribute_id = 169
			WHERE o.created_at > '$from'
			AND o.`status` IN ('pending','southbay_sap_error','southbay_sap_confirm_fail')
			AND o.store_id IN (SELECT southbay_store_code FROM southbay_config_store WHERE southbay_function_code = 'futures')
			AND item.product_options LIKE '%month_delivery_date%'
        ";


        $items = $connection->fetchAll($sql);

        return $this->filterItems($items);
    }

    private function filterItems($items)
    {
        $result = [];

        foreach ($items as $item) {
            if ($item['unit'] > 1) {
                $result[] = $item;
            }
        }

        return $result;
    }

}
