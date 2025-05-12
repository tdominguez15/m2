<?php

namespace Southbay\ReturnProduct\Console\Command;

use Southbay\ReturnProduct\Helper\SendSapRtvRequest;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendApprovalNotificationCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:return_product:send:approval:notification')
            ->addArgument('return_product_id', null, 'Return product id')
            ->setDescription('Send approval notification');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $return_product_id = $input->getArgument('return_product_id');

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $r
         */
        $r = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository');

        $model = $r->findById($return_product_id);

        if (is_null($model)) {
            $output->writeln('Return product not found');
            return 0;
        }

        /**
         * @var \Southbay\ReturnProduct\Helper\SendNotification $s
         */
        $s = $objectManager->get('Southbay\ReturnProduct\Helper\SendNotification');
        $s->send($model, $model->getCountryCode(), true);

        return 1;
    }
}
