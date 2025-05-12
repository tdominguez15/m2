<?php

namespace Southbay\CustomCheckout\Console\Command;

use Southbay\Product\Model\Import\ProductImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestUpdateOrder extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:test:update:order')
            ->addArgument('id', null, 'magento order id')
            ->addOption('sku', null, InputOption::VALUE_REQUIRED, 'sku')
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'size')
            ->addOption('flow', null, InputOption::VALUE_REQUIRED, 'flow number')
            ->addOption('qty', null, InputOption::VALUE_REQUIRED, ' new qty')
            ->setDescription('Update order product');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $p = new ProductImporter();
        $attrs = $p->findAttributeByCode('southbay_size');

        $id = $input->getArgument('id');
        $sku_arg = $input->getOption('sku');
        $size = $input->getOption('size');
        $flow = $input->getOption('flow');
        $flow = "month_delivery_date_" . $flow;
        $new_qty = $input->getOption('qty');

        $options = $attrs->getOptions();
        $size_option_id = '0';

        foreach ($options as $option) {
            if ($option->getLabel() == $size) {
                $size_option_id = $option->getValue();
                break;
            }
        }

        if ($size_option_id == '0') {
            $output->writeln('No existe el talle ' . $size);
            return 0;
        }

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $objectManager->get('Magento\Sales\Model\Order')->load($id);

        if (!$order->getId()) {
            $output->writeln('No existe la orden ' . $id);
            return 0;
        }

        $items = $order->getAllItems();
        $sku_full = $sku_arg . '/' . $size;
        $old_qty = null;
        $_item = null;
        $_item_with_options = null;

        foreach ($items as $item) {
            $sku = $item->getSku();

            if (str_starts_with($sku, $sku_arg)) {
                $data = $item->getProductOptions();

                if (isset($data['info_buyRequest'][$flow][$size_option_id])) {
                    $old_qty = $data['info_buyRequest'][$flow][$size_option_id];
                    $data['info_buyRequest'][$flow][$size_option_id] = $new_qty;
                    $item->setProductOptions($data);
                    $_item_with_options = $item;
                }

                if ($sku == $sku_full) {
                    $_item = $item;
                }

                if (!is_null($old_qty) && !is_null($_item)) {
                    break;
                }
            }
        }

        if (!is_null($old_qty) && !is_null($_item)) {
            $output->writeln('before. total qty: ' . $order->getTotalQtyOrdered() . '; total: ' . $order->getSubtotal());

            $old_row_total = $_item->getRowTotal();
            $old_item_qty = $_item->getQtyOrdered();

            $qty = $old_item_qty - $old_qty;
            $qty += $new_qty;

            $_item_with_options->save();

            $_item->setQtyOrdered($qty);
            $_item->setRowTotal($item->getQtyOrdered() * $item->getPrice());
            $_item->setBaseRowTotal($item->getRowTotal());
            $_item->setRowTotalInclTax($item->getRowTotal());
            $_item->setBaseRowTotalInclTax($item->getRowTotal());
            $_item->save();

            $total_qty = $order->getTotalQtyOrdered() - $old_item_qty;
            $total_qty += $_item->getQtyOrdered();

            $total = $order->getSubtotal() - $old_row_total;
            $total += $_item->getRowTotal();

            $order->setTotalQtyOrdered($total_qty);
            $order->setBaseGrandTotal($total);
            $order->setBaseSubtotal($total);
            $order->setGrandTotal($total);
            $order->setSubtotal($total);
            $order->setSubtotalInclTax($total);
            $order->setBaseTotalDue($total);
            $order->setTotalDue($total);
            $order->save();

            $output->writeln('Item actualizado: ' . $sku_full);
            $output->writeln('after. total qty: ' . $order->getTotalQtyOrdered() . '; total: ' . $order->getSubtotal());

            return 1;
        }

        $output->writeln('old_qty: ' . ($old_qty ?? 'n/a') . '; item: ' . ($_item ? 's' : 'n'));
        $output->writeln('No se encontro el item para el sku ' . $sku_full);

        return 0;
    }
}
