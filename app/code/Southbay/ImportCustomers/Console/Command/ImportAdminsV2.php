<?php

namespace Southbay\ImportCustomers\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\User\Model\UserFactory;
use Magento\Framework\App\ResourceConnection;

class ImportAdminsV2 extends Command
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
                "MAIL" => "rzacarias@exologistica.com",
                "NOMBRE" => "zacarias",
                "APELLIDO" => "zacarias",
                "PAIS" => "AR",
                "ROL IDS" => "20",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "wmarquez@exologistica.com",
                "NOMBRE" => "marquez",
                "APELLIDO" => "marquez",
                "PAIS" => "AR",
                "ROL IDS" => "20",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "smazoletti@exologistica.com",
                "NOMBRE" => "mazoletti",
                "APELLIDO" => "mazoletti",
                "PAIS" => "AR",
                "ROL IDS" => "20",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "hgonzalez@exologistica.com",
                "NOMBRE" => "gonzalez",
                "APELLIDO" => "gonzalez",
                "PAIS" => "AR",
                "ROL IDS" => "20",
                "ROL" => "reception,control_qa"
            ],
            [
                "MAIL" => "mariela.villavieja@southbay.com.ar",
                "NOMBRE" => "Mariela",
                "APELLIDO" => "Villavieja",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "ignacio.ferrari@southbay.com.ar",
                "NOMBRE" => "Ignacio",
                "APELLIDO" => "Ferrari",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "pedro.boll@southbay.com.ar",
                "NOMBRE" => "Pedro",
                "APELLIDO" => "Boll",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "maria.garcia2@southbay.com.ar",
                "NOMBRE" => "Maria Ines",
                "APELLIDO" => "Garcia",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "marcelo.oleiro@southbay.com.ar",
                "NOMBRE" => "Marcelo",
                "APELLIDO" => "Oleiro",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "maximiliano.insua@southbay.com.ar",
                "NOMBRE" => "Maximiliano",
                "APELLIDO" => "Insua",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "gabriel.navotka@southbay.com.ar",
                "NOMBRE" => "Gabriel",
                "APELLIDO" => "Navotka",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "ernesto.mazzitelli@southbay.com.ar",
                "NOMBRE" => "Ernesto",
                "APELLIDO" => "Mazzitelli",
                "PAIS" => "AR",
                "ROL IDS" => "24,26",
                "ROL" => "checker,approval",
                "Column7" => ">7kUSD"
            ],
            [
                "MAIL" => "sofiaayelen.velazquez@southbay.com.ar",
                "NOMBRE" => "Sofia",
                "APELLIDO" => "Velazquez",
                "PAIS" => "AR",
                "ROL IDS" => 24,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "rcarrasco@farmared.com.uy",
                "NOMBRE" => "carrasco",
                "APELLIDO" => "carrasco",
                "PAIS" => "UR",
                "ROL IDS" => "21,23",
                "ROL" => "reception,control_qa "
            ],
            [
                "MAIL" => "ambiental@farmared.com.uy",
                "NOMBRE" => "ambiental",
                "APELLIDO" => "ambiental",
                "PAIS" => "UR",
                "ROL IDS" => "21,23",
                "ROL" => "reception,control_qa "
            ],
            [
                "MAIL" => "gonzalo.rodriguez@southbay.com.uy",
                "NOMBRE" => "Gonzalo",
                "APELLIDO" => "Rodriguez",
                "PAIS" => "UR",
                "ROL IDS" => 25,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "lorena.carnelli@southbay.com.uy",
                "NOMBRE" => "Lorena",
                "APELLIDO" => "Carnelli",
                "PAIS" => "UR",
                "ROL IDS" => 25,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "luciana.rodriguez@southbay.com.uy",
                "NOMBRE" => "Luciana",
                "APELLIDO" => "Rodriguez",
                "PAIS" => "UR",
                "ROL IDS" => 25,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "veronica.franco@southbay.com.uy",
                "NOMBRE" => "Veronica",
                "APELLIDO" => "Franco",
                "PAIS" => "UR",
                "ROL IDS" => "25,34",
                "ROL" => "checker,approval"
            ],
            [
                "MAIL" => "agustina.ferres@southbay.com.uy",
                "NOMBRE" => "Agustina",
                "APELLIDO" => "Ferres",
                "PAIS" => "UR",
                "ROL IDS" => 25,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "florencia.tejeragutierrez@southbay.com.uy",
                "NOMBRE" => "Florencia",
                "APELLIDO" => "Tejera Gutierrez",
                "PAIS" => "UR",
                "ROL IDS" => 25,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "matias.leis@southbay.com.uy",
                "NOMBRE" => "Matias",
                "APELLIDO" => "Leis",
                "PAIS" => "UR",
                "ROL IDS" => 25,
                "ROL" => "checker"
            ],
            [
                "MAIL" => "adriana.farras@southbay.com.uy",
                "NOMBRE" => "Adriana",
                "APELLIDO" => "Farras",
                "PAIS" => "AR",
                "ROL IDS" => 28,
                "ROL" => "approval",
                "Column7" => "ARG >15kUSD; URU >10kUSD",
                "Column8" => "Approver MF"
            ],
            [
                "MAIL" => "federico.tortora@southbay.com.ar",
                "NOMBRE" => "Federico",
                "APELLIDO" => "Tortora",
                "PAIS" => "AR",
                "ROL IDS" => 32,
                "ROL" => "approval_good",
                "Column7" => "ARG >100kUSD; URU>50kUSD",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "gonzalo.policastro@southbay.com.ar",
                "NOMBRE" => "Gonzalo",
                "APELLIDO" => "Policastro",
                "PAIS" => "AR",
                "ROL IDS" => 32,
                "ROL" => "approval_good",
                "Column7" => "ARG >100kUSD; URU>50kUSD",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "jeffrey.mitchell@southbay.com.ar",
                "NOMBRE" => "Jeffrey",
                "APELLIDO" => "Mitchell",
                "PAIS" => "AR",
                "ROL IDS" => 33,
                "ROL" => "approval_good",
                "Column7" => "ARG >500kUSD; URU>250kUSD",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "sebastian.lopez@southbay.com.ar",
                "NOMBRE" => "Sebastián",
                "APELLIDO" => "López",
                "PAIS" => "AR",
                "ROL IDS" => 33,
                "ROL" => "approval_good",
                "Column7" => "ARG y URU (clientes externos)",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "micaela.wajs@southbay.com.ar",
                "NOMBRE" => "Micaela",
                "APELLIDO" => "Wajs",
                "PAIS" => "AR",
                "ROL IDS" => 33,
                "ROL" => "approval_good",
                "Column7" => "ARG y URU (clientes externos)",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "virginia.espeche@southbay.com.ar",
                "NOMBRE" => "Virginia",
                "APELLIDO" => "Espeche",
                "PAIS" => "AR",
                "ROL IDS" => 33,
                "ROL" => "approval_good",
                "Column7" => "ARG y URU (clientes internos)",
                "Column8" => "Approver MB"
            ]
        ];
    }
}
