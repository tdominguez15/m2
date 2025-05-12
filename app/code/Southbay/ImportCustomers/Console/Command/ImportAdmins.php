<?php

namespace Southbay\ImportCustomers\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\User\Model\UserFactory;
use Magento\Framework\App\ResourceConnection;

class ImportAdmins extends Command
{
    protected $state;
    protected $userFactory;
    protected $resourceConnection;

    public function __construct(
        State $state,
        UserFactory $userFactory,
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
        $createdUserIds= [];
        $adminData = $this->getData();

        foreach ($adminData as $adminDataItem) {
            $userEmail = $adminDataItem['MAIL'];
            if(empty($adminDataItem['NOMBRE'])){
                $userName = $adminDataItem['NOMBRE_CLIENTE'];
                $userLastName = $adminDataItem['NOMBRE_CLIENTE'];
            }
            else{
                $userName = $adminDataItem['NOMBRE'];
                $userLastName = $adminDataItem['APELLIDO'];
            }
            $userPassword = "southbay2024";

            // Create admin user
            $user = $this->userFactory->create();
            $user->setUsername($userEmail);
            $user->setFirstname($userName);
            $user->setLastname($userLastName);
            $user->setEmail($userEmail);
            $user->setPassword($userPassword);
            $user->setIsActive(true);
            $user->setRoleId(6);

            try {
                $user->save();
                $output->writeln('<info>Admin user created successfully: ' . $userEmail . '</info>');
                $createdUserIds[] = $user->getId();
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to create admin user: ' . $userEmail . '</error>');
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }

        $output->writeln('<info>Admin users imported successfully.</info>');

        // Update the admin_passwords table
       // $this->queryAdminPasswordsTable($createdUserIds);

        return 1;
    }

    protected function queryAdminPasswordsTable($createdUserIds)
    {
        $expiresDate = '2024-01-01';
        $connection = $this->resourceConnection->getConnection();

        $tableName = $connection->getTableName('admin_passwords');
        $userIds = implode(',', $createdUserIds);

        $sql = "UPDATE $tableName SET expires = '$expiresDate' WHERE user_id IN ($userIds)";
        $connection->query($sql);


        return true;
    }

    /**
     * Placeholder function for getting admin data.
     * Replace this with your actual data retrieval logic.
     *
     * @return array
     */
    public function getData()
    {
        $data =
            [
                0 => [
                    'MAIL' => 'rzacarias@exologistica.com',
                    'NOMBRE' => '',
                    'APELLIDO' => '',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Recepcion CD + CC Merc Buena',
                    'MONTO A APROBAR' => '',
                ],
                1 => [
                    'MAIL' => 'wmarquez@exologistica.com',
                    'NOMBRE' => '',
                    'APELLIDO' => '',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Recepcion CD + CC Merc Buena',
                    'MONTO A APROBAR' => '',
                ],
                2 => [
                    'MAIL' => 'smazoletti@exologistica.com',
                    'NOMBRE' => '',
                    'APELLIDO' => '',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Recepcion CD + CC Merc Buena',
                    'MONTO A APROBAR' => '',
                ],
                3 => [
                    'MAIL' => 'hgonzalez@exologistica.com',
                    'NOMBRE' => '',
                    'APELLIDO' => '',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Recepcion CD + CC Merc Buena',
                    'MONTO A APROBAR' => '',
                ],
                4 => [
                    'MAIL' => 'mariela.villavieja@southbay.com.ar',
                    'NOMBRE' => 'Mariela',
                    'APELLIDO' => 'Villavieja',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                5 => [
                    'MAIL' => 'ignacio.ferrari@southbay.com.ar',
                    'NOMBRE' => 'Ignacio',
                    'APELLIDO' => 'Ferrari',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                6 => [
                    'MAIL' => 'pedro.boll@southbay.com.ar',
                    'NOMBRE' => 'Pedro',
                    'APELLIDO' => 'Boll',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                7 => [
                    'MAIL' => 'maria.garcia2@southbay.com.ar',
                    'NOMBRE' => 'Maria Ines',
                    'APELLIDO' => 'Garcia',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                8 => [
                    'MAIL' => 'marcelo.oleiro@southbay.com.ar',
                    'NOMBRE' => 'Marcelo',
                    'APELLIDO' => 'Oleiro',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                9 => [
                    'MAIL' => 'maximiliano.insua@southbay.com.ar',
                    'NOMBRE' => 'Maximiliano',
                    'APELLIDO' => 'Insua',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                10 => [
                    'MAIL' => 'gabriel.navotka@southbay.com.ar',
                    'NOMBRE' => 'Gabriel',
                    'APELLIDO' => 'Navotka',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                11 => [
                    'MAIL' => 'ernesto.mazzitelli@southbay.com.ar',
                    'NOMBRE' => 'Ernesto',
                    'APELLIDO' => 'Mazzitelli',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User + Approver MF',
                    'MONTO A APROBAR' => '>7kUSD',
                ],
                12 => [
                    'MAIL' => 'sofiaayelen.velazquez@southbay.com.ar',
                    'NOMBRE' => 'Sofia',
                    'APELLIDO' => 'Velazquez',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                13 => [
                    'MAIL' => 'rcarrasco@farmared.com.uy',
                    'NOMBRE' => '',
                    'APELLIDO' => '',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Recepcion CD + CC',
                    'MONTO A APROBAR' => '',
                ],
                14 => [
                    'MAIL' => 'ambiental@farmared.com.uy',
                    'NOMBRE' => '',
                    'APELLIDO' => '',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Recepcion CD + CC',
                    'MONTO A APROBAR' => '',
                ],
                15 => [
                    'MAIL' => 'gonzalo.rodriguez@southbay.com.uy',
                    'NOMBRE' => 'Gonzalo',
                    'APELLIDO' => 'Rodriguez',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                16 => [
                    'MAIL' => 'lorena.carnelli@southbay.com.uy',
                    'NOMBRE' => 'Lorena',
                    'APELLIDO' => 'Carnelli',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                17 => [
                    'MAIL' => 'luciana.rodriguez@southbay.com.uy',
                    'NOMBRE' => 'Luciana',
                    'APELLIDO' => 'Rodriguez',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                18 => [
                    'MAIL' => 'veronica.franco@southbay.com.uy',
                    'NOMBRE' => 'Veronica',
                    'APELLIDO' => 'Franco',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Super User + Approver MF',
                    'MONTO A APROBAR' => '>2kUSD',
                ],
                19 => [
                    'MAIL' => 'agustina.ferres@southbay.com.uy',
                    'NOMBRE' => 'Agustina',
                    'APELLIDO' => 'Ferres',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                20 => [
                    'MAIL' => 'florencia.tejeragutierrez@southbay.com.uy',
                    'NOMBRE' => 'Florencia',
                    'APELLIDO' => 'Tejera Gutierrez',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                21 => [
                    'MAIL' => 'matias.leis@southbay.com.uy',
                    'NOMBRE' => 'Matias',
                    'APELLIDO' => 'Leis',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'URU',
                    'ROL' => 'Super User',
                    'MONTO A APROBAR' => '',
                ],
                22 => [
                    'MAIL' => 'adriana.farras@southbay.com.uy',
                    'NOMBRE' => 'Adriana',
                    'APELLIDO' => 'Farras',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Approver MF',
                    'MONTO A APROBAR' => 'ARG >15kUSD; URU >10kUSD'
                ],
                23 => [
                    'MAIL' => 'federico.tortora@southbay.com.ar',
                    'NOMBRE' => 'Federico',
                    'APELLIDO' => 'Tortora',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Approver MB',
                    'MONTO A APROBAR' => 'ARG >100kUSD; URU>50kUSD'
                ],
                24 => [
                    'MAIL' => 'gonzalo.policastro@southbay.com.ar',
                    'NOMBRE' => 'Gonzalo',
                    'APELLIDO' => 'Policastro',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Approver MB',
                    'MONTO A APROBAR' => 'ARG >100kUSD; URU>50kUSD'
                ],
                25 => [
                    'MAIL' => 'jeffrey.mitchell@southbay.com.ar',
                    'NOMBRE' => 'Jeffrey',
                    'APELLIDO' => 'Mitchell',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Approver MB',
                    'MONTO A APROBAR' => 'ARG >500kUSD; URU>250kUSD'
                ],
                26 => [
                    'MAIL' => 'sebastian.lopez@southbay.com.ar',
                    'NOMBRE' => 'Sebastián',
                    'APELLIDO' => 'López',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Approver MB',
                    'MONTO A APROBAR' => 'ARG y URU (clientes externos)'
                ],
                27 => [
                    'MAIL' => 'micaela.wajs@southbay.com.ar',
                    'NOMBRE' => 'Micaela',
                    'APELLIDO' => 'Wajs',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Approver MB',
                    'MONTO A APROBAR' => 'ARG y URU (clientes externos)'
                ],
                28 => [
                    'MAIL' => 'virginia.espeche@southbay.com.ar',
                    'NOMBRE' => 'Virginia',
                    'APELLIDO' => 'Espeche',
                    'NOMBRE_CLIENTE' => 'Southbay',
                    'SO' => '',
                    'PAIS' => 'ARG',
                    'ROL' => 'Approver MB',
                    'MONTO A APROBAR' => 'ARG y URU (clientes internos)'
                ]
            ];
        return $data;
    }
}
