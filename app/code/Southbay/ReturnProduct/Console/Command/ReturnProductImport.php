<?php

namespace Southbay\ReturnProduct\Console\Command;

use Southbay\ReturnProduct\Helper\SendSapRtvRequest;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReturnProductImport extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:return_product:import')->setDescription('Import return product');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var SendSapRtvRequest $s
         */
        $s = $objectManager->get(SendSapRtvRequest::class);

        $s->send(10);
        // $s->checkNC(9006);

        $s->checkSapInterfacePendingToEnd();

        return 1;
    }

    protected function executeasdasd(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var SouthbayInvoiceRepository $repo
         */
        $repo = $objectManager->get(SouthbayInvoiceRepository::class);
        $invoice = $repo->findById(3);

        $output->writeln($invoice->getInvoiceDate());

        $date = strtotime($invoice->getInvoiceDate());
        $output->writeln($date);
        $output->writeln(date('d/M/Y', $date));
        $output->writeln(date('d.m.Y', $date));

        return 1;
    }

    protected function executetets(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $r
         */
        $r = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository');

        $model = $r->findById(6);

        /**
         * @var \Southbay\ReturnProduct\Helper\SendNotification $s
         */
        $s = $objectManager->get('Southbay\ReturnProduct\Helper\SendNotification');
        $s->send($model, $model->getCountryCode(), true);

        return 1;
    }

    protected function executerr(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->get('Magento\Email\Model\ResourceModel\Template\CollectionFactory');

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $collectionFactory->create();
        $collection->load();
        $items = $collection->getItems();

        foreach ($items as $item) {
            $output->writeln(json_encode($item->getData()));
        }

        return 1;
    }

    protected function execute33(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $factory = $objectManager->get('Magento\Directory\Model\CountryFactory');
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $factory->create()->getCollection();
        $collection->load();
        $items = $collection->getItems();

        $count = 0;

        foreach ($items as $item) {
            $output->writeln(json_encode($item->getData()));
        }

        return 1;
    }

    protected function execute22(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $repository
         */
        $repository = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository');
        $r = $repository->searchPendingReception('6', 1);

        $output->writeln('aaa: ' . $r->count());

        return 1;
    }

    protected function executeOLD(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Southbay\ReturnProduct\Importer\InvoiceImporter $importer
         */
        $importer = $objectManager->get('Southbay\ReturnProduct\Importer\InvoiceImporter');
        $importer->import('facturado alamo  enero 2024.xlsx');

        return 1;
    }

    protected function execute444(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository $repository
         */
        $repository = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository');
        $list = $repository->searchBySku('CW6107-010', 'good', '0000456411', 'AR');

        $output->writeln('data: ' . json_encode($list, JSON_PRETTY_PRINT));

        return 1;
    }

    protected function execute2(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $repository = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem');
        $factory = $objectManager->get('Southbay\ReturnProduct\Model\SouthbayInvoiceItemFactory');

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem $model
         */
        $model = $factory->create();
        $model->setInvoiceId(1);
        $model->setSku('CU3528-601');
        $model->setName('W NIKE RUN SWIFT 2');
        $model->setSize('10');
        $model->setQty(8);
        $model->setAmount(450522.08);
        $model->setUnitPrice(56315.26);
        $model->setNetAmount(414480.32);

        $repository->save($model);

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem $model
         */
        $model = $factory->create();
        $model->setInvoiceId(1);
        $model->setSku('DC3728-401');
        $model->setName('NIKE REVOLUTION 6 NN');
        $model->setSize('10');
        $model->setQty(74);
        $model->setAmount(3777856.14);
        $model->setUnitPrice(51052.11);
        $model->setNetAmount(3475627.56);

        $repository->save($model);

        return 1;
    }

    protected function execute0(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $repository = $objectManager->get('Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice');

        $factory = $objectManager->get('Southbay\ReturnProduct\Model\SouthbayInvoiceFactory');

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoice $model
         */
        $model = $factory->create();
        $model->setCustomerCode('0000239672');
        $model->setCustomerName('ADOLFO ZAKIAN S.A.');
        $model->setCustomerShipToCode('0000239967');
        $model->setDivCode('20');
        $model->setInvoiceDate('2024-01-12');
        $model->setIntInvoiceNum(4507324871);
        $model->setInvoiceRef('0090A01153266');

        $repository->save($model);

        return 1;
    }
}
