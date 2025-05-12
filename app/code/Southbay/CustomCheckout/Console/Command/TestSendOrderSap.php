<?php

namespace Southbay\CustomCheckout\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestSendOrderSap extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:test:send:order')
            ->setDescription('Send orders to sap');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->get(\Southbay\ApproveOrders\Cron\SendOrdersToSap::class)->sendFuture();

        return 1;
    }
}
