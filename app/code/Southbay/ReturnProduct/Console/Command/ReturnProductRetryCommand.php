<?php

namespace Southbay\ReturnProduct\Console\Command;

use Composer\Console\Input\InputArgument;
use Composer\Console\Input\InputOption;
use Southbay\ReturnProduct\Helper\SendSapRtvRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument as InputArgumentAlias;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReturnProductRetryCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:return_product:retry')
            ->addArgument('id', InputArgumentAlias::OPTIONAL, 'rtv id')
            ->addOption('find-by-sap-interface-id', null, null, 'Find by sap interface id')
            ->setDescription('retry send to sap');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = null;
        $find_by_sap_interface_id = false;

        if ($input->hasArgument('id')) {
            $id = $input->getArgument('id');
        }

        if ($input->hasOption('find-by-sap-interface-id')) {
            $find_by_sap_interface_id = true;
        }

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

        /**
         * @var SendSapRtvRequest $s
         */
        $s = $objectManager->get(SendSapRtvRequest::class);

        $con = $resource->getConnection();

        $sql = "";

        if (empty($id)) {
            $sql = "SELECT r.southbay_return_id as id FROM southbay_return r where r.southbay_return_id not in (select s.southbay_sap_interface_ref from southbay_sap_interface s where southbay_sap_interface_from = 'rtv') and r.southbay_return_status = 'confirmed'";
        } else if (!$find_by_sap_interface_id) {
            $sql = "SELECT r.southbay_return_id as id FROM southbay_return r where r.southbay_return_id = $id and r.southbay_return_status = 'confirmed'";
        }

        if (!empty($sql)) {
            $rows = $con->fetchAll($sql);

            $first = true;

            if (!empty($rows)) {
                foreach ($rows as $r) {
                    $output->writeln('Retry all order #' . $r['id']);

                    if ($first) {
                        $first = false;
                    } else {
                        sleep(5);
                    }
                    $s->send($r['id']);
                }
            }
        }

        if (!empty($id) && !$find_by_sap_interface_id) {
            return 1;
        }

        $sql = "select s.southbay_sap_interface_id as id, s.southbay_sap_interface_ref as ref from southbay_sap_interface s where southbay_sap_interface_from = 'rtv' and s.southbay_sap_interface_status = 'error'";
        if ($find_by_sap_interface_id && !empty($id)) {
            $sql .= " and s.southbay_sap_interface_ref = $id";
        }
        $rows = $con->fetchAll($sql);

        if (!empty($rows)) {
            foreach ($rows as $r) {
                $output->writeln('Retry order #' . $r['ref'] . ' subdoc #' . $r['id']);
                $model = $s->findSapRequest($r['id']);
                if (!is_null($model)) {
                    $output->writeln('OLD RESPONSE: ');
                    $output->writeln($model->getResponse());
                    $s->retry($model);
                } else {
                    $output->writeln('Model not exists');
                }
            }
        }

        return 1;
    }
}
