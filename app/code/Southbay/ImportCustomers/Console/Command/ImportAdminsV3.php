<?php

namespace Southbay\ImportCustomers\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\User\Model\UserFactory;
use Magento\Framework\App\ResourceConnection;

class ImportAdminsV3 extends Command
{
    protected $state;
    protected $userFactory;
    protected $resourceConnection;

    public function __construct(
        State              $state,
        UserFactory        $userFactory,
        ResourceConnection $resourceConnection
    )
    {
        $this->state = $state;
        $this->userFactory = $userFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:admins:import')
            ->setDescription('Import admins from predefined data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\Authorization\Model\ResourceModel\Role $role
         */
        $role = $objectManager->get('Magento\Authorization\Model\UserContextInterface');

        $data = $this->getData();

        foreach ($data as $user_data) {
            $user = $this->userFactory->create();
            $user->setUsername($user_data['MAIL']);
            $user->setFirstname($user_data['NOMBRE']);
            $user->setLastname($user_data['APELLIDO']);
            $user->setEmail($user_data['MAIL']);
            $user->setPassword("Southbay2024$");
            $user->setIsActive(true);
            $user->setRoleId($user_data['ROL IDS']);

            try {
                $user->save();

                $output->writeln('<info>Admin user created successfully: ' . $user->getEmail() . '</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to create admin user: ' . $user->getEmail() . '</error>');
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }

        return 1;
    }

    public function getDatass()
    {
        return [
            [
                "MAIL" => "rzacarias10002@exologistica.com",
                "NOMBRE" => "zacarias",
                "APELLIDO" => "zacarias",
                "PAIS" => "AR",
                "ROL IDS" => "20",
                "ROL" => "reception,control_qa"
            ]
        ];
    }

    public function getData()
    {
        return [
            [
                "MAIL" => "cecilia.ducrey@southbay.com.ar",
                "NOMBRE" => "Cecilia",
                "APELLIDO" => "Ducrey",
                "PAIS" => "AR",
                "ROL IDS" => "82",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "mariano.gelis@southbay.com.ar",
                "NOMBRE" => "Mariano",
                "APELLIDO" => "Gelis",
                "PAIS" => "AR",
                "ROL IDS" => "82",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "tamara.debenedetti@southbay.com.ar",
                "NOMBRE" => "Tamara",
                "APELLIDO" => "De Benedetti",
                "PAIS" => "AR",
                "ROL IDS" => "82",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "santiago.calvo@southbay.com.ar",
                "NOMBRE" => "Santiago",
                "APELLIDO" => "Calvo",
                "PAIS" => "AR",
                "ROL IDS" => "82",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "juanpablo.navarro@southbay.com.ar",
                "NOMBRE" => "Juan Pablo",
                "APELLIDO" => "Navarro",
                "PAIS" => "AR",
                "ROL IDS" => "82",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "federico.iceta@southbay.com.ar",
                "NOMBRE" => "Federico",
                "APELLIDO" => "Iceta",
                "PAIS" => "AR",
                "ROL IDS" => "82",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "mariano.gimenez@southbay.com.ar",
                "NOMBRE" => "Mariano",
                "APELLIDO" => "Gimenez",
                "PAIS" => "AR",
                "ROL IDS" => "82",
                "ROL" => "reception,control_qa"
            ],
        ];
    }
}
