<?php

namespace Southbay\CustomCheckout\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFuturesOrderReport extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:generate:future:report')
            ->setDescription('Generate future orders report');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
            'to' => '2024-08-29',
            'store_id' => '2',
            'sold_to_list' => []
        ]);

        return 1;
    }
}
