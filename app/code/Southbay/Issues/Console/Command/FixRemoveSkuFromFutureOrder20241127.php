<?php

namespace Southbay\Issues\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixRemoveSkuFromFutureOrder20241127 extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:fix:future:orders:20241127')
            ->setDescription('Fix-20241127: se deben quietar una serie de productos de unas ordenes (solicitado por ceci y mariano)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orders_nro = [
            '2000000747',
            '2000000749'
        ];

        $skus = [
            'DZ2795-202',
            'FZ8605-101',
            'HF2793-700',
            'HF0823-008',
            'IH3583-999',
            'DX4215-100',
            'IH3576-999',
            'HF7545-100',
            'IH4501-001',
            'FD2596-200',
            'HQ2592-300',
            'HQ2592-005',
            'HQ2593-602',
            'HQ2593-004',
            'FQ8138-200',
            'IH0401-001',
            'IH3586-999',
            'IH3575-999'
        ];

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');

        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('increment_id', ['in' => $orders_nro]);
        $collection->addFieldToFilter('status', ['eq' => 'pending']);

        $orders = $collection->getItems();

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        foreach ($orders as $order) {
            $output->writeln(sprintf('<info>%s</info>', $order->getIncrementId()));
            $items = $order->getItems();
            $has_changes = false;
            $new_items = [];

            $total_qty = 0;
            $total_money = 0;

            /**
             * @var \Magento\Sales\Model\Order\Item $item
             */
            foreach ($items as $item) {
                list($sku) = explode('/', $item->getSku());
                if (in_array($sku, $skus)) {
                    $has_changes = true;
                    $order->addCommentToStatusHistory('Fix 20241127: se elimino el item: ' . $item->getSku() . '. Cantidad: ' . $item->getQtyOrdered());
                    $item->delete();
                    $output->writeln(sprintf('<info>remove %s</info>', $item->getSku()));
                } else {
                    $new_items[] = $item;
                    $total_qty += $item->getQtyOrdered();
                    $total_money += $item->getQtyOrdered() * $item->getPrice();
                }
            }

            // if ($has_changes) {
            //    $order->setItems($new_items);

            $order->setBaseGrandTotal($total_money);
            $order->setBaseSubtotal($total_money);

            $order->setBaseSubtotalInclTax($total_money);
            $order->setBaseTotalDue($total_money);
            $order->setSubtotalInclTax($total_money);
            $order->setTotalDue($total_money);
            $order->setWeight($total_qty);

            $order->setSubtotal($total_money);
            $order->setGrandTotal($total_money);
            $order->setTotalQtyOrdered($total_qty);

            $order->save();
            // }
        }

        return 1;
    }

}
