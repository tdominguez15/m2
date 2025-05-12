<?php

namespace Southbay\ReturnProduct\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunRtvSaveSapDocumentsCron extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:return_product:run-cron')
            ->setDescription('run cron sap doc');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\ReturnProduct\Cron\RtvSaveSapDocumentsCronModel $cron
         */
        $cron = $objectManager->get('Southbay\ReturnProduct\Cron\RtvSaveSapDocumentsCronModel');
        $cron->execute();

        return 1;
    }
}
