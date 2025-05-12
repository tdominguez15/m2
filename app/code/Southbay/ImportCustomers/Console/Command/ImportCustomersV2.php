<?php

namespace Southbay\ImportCustomers\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Model\CustomerConfigFactory;
use Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory as SoldToCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Command to import customers from predefined data.
 */
class ImportCustomersV2 extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerConfigFactory
     */
    protected $customerConfigFactory;

    /**
     * @var SoldToCollectionFactory
     */
    protected $soldToCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * ImportCustomers constructor.
     *
     * @param State $state
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerConfigFactory $customerConfigFactory
     * @param SoldToCollectionFactory $soldToCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        State                       $state,
        CustomerFactory             $customerFactory,
        StoreManagerInterface       $storeManager,
        CustomerConfigFactory       $customerConfigFactory,
        SoldToCollectionFactory     $soldToCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        EncryptorInterface          $encryptor

    )
    {
        $this->state = $state;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerConfigFactory = $customerConfigFactory;
        $this->soldToCollectionFactory = $soldToCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->encryptor = $encryptor;
        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('southbay:customers:import2')
            ->setDescription('Import users from predefined data');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $userData = $this->groupDataperMail();;
        foreach ($userData as $userDataItem) {
            $userEmail = $userDataItem['MAIL'];
            if (empty($userEmail)) {
                break;
            }

            $userName = $userDataItem['NOMBRE'];
            $userLastName = $userDataItem['APELLIDO'];

            // Create customer
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId(1);
            $customer->setEmail($userEmail);
            $customer->setFirstname($userName);
            $customer->setLastname($userLastName);
            $password = 'Southbay2024$';
            $customer->setData('assistance_allowed', 1);
            $customer->setPassword($password);

            try {
                $customer->save();
                $country = $userDataItem['PAIS'];
                $soCodes = explode(',', $userDataItem['SO']);

                $collection = $this->soldToCollectionFactory->create()->addFieldToFilter('southbay_sold_to_customer_code', ['in' => $soCodes]);
                $soldsToArray = [];

                foreach ($collection as $item) {
                    $soldsToArray[] = $item->getId();
                }
                $soldsToString = implode(',', $soldsToArray);
                $customerConfig = $this->customerConfigFactory->create();
                $customerConfig->setMagentoCustomerEmail($userEmail);
                $customerConfig->setSoldToIds($soldsToString);
                $customerConfig->setCountriesCodes($country);
                $customerConfig->setFunctionsCodes('rtv,futures');

                $customerConfig->save();

                $output->writeln('<info>Customer and CustomerConfig entry created successfully for customer: ' . $userEmail . '</info>');
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to create customer: ' . $userEmail . '</error>');
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }

        $output->writeln('<info>Users imported successfully.</info>');
        return 1;
    }

    public function groupDataperMail()
    {
        $userData = $this->getData();
        $groupedUserData = [];

        foreach ($userData as $userDataItem) {
            $userEmail = $userDataItem['MAIL'];
            $userSO = $userDataItem['SO'];

            // Verificar si el correo electrónico está vacío
            if (empty($userEmail)) {
                continue;
            }

            // $userEmail =trim($userDataItem['MAIL']);
            $userDataItem['MAIL'] = trim($userEmail);

            // Verificar si el correo electrónico ya existe en el array agrupado
            if (isset($groupedUserData[$userEmail])) {
                // Si ya existe, concatenar el valor SO al valor existente en el array agrupado
                $groupedUserData[$userEmail]['SO'] .= ',' . $userSO;
            } else {
                // Si es el primer registro para este correo electrónico, mantener los valores existentes y establecer SO
                $groupedUserData[$userEmail] = $userDataItem;
            }
        }
        return $groupedUserData;
    }


    public function getData()
    {
        return [
            [
                "MAIL" => "fercaste@vera.com.uy",
                "NOMBRE" => "ALCOIANO SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "ALCOIANO SRL",
                "SO" => 1400000002,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "alcoiano.info@gmail.com",
                "NOMBRE" => "ALCOIANO SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "ALCOIANO SRL",
                "SO" => 1400000002,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "goncalves.nestor@gmail.com",
                "NOMBRE" => "JUAN GONCALVES Y CIA.SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "JUAN GONCALVES Y CIA.SRL",
                "SO" => 1400000004,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "dariostar.sergiogarcia@gmail.com",
                "NOMBRE" => "DARIOSTAR S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "DARIOSTAR S.A.",
                "SO" => 1400000006,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "dariostar.nataliarodriguez@gmail.com",
                "NOMBRE" => "DARIOSTAR S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "DARIOSTAR S.A.",
                "SO" => 1400000006,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "dariostar.fabianabentancor@gmail.com",
                "NOMBRE" => "DARIOSTAR S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "DARIOSTAR S.A.",
                "SO" => 1400000006,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "sebastianruiz@wandamel.com",
                "NOMBRE" => "WANDAMEL S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "WANDAMEL S.A.",
                "SO" => 1400000007,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "nancyadministracion@wandamel.com",
                "NOMBRE" => "WANDAMEL S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "WANDAMEL S.A.",
                "SO" => 1400000007,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "yolanda@wandamel.com",
                "NOMBRE" => "WANDAMEL S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "WANDAMEL S.A.",
                "SO" => 1400000007,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "aldo.aiub@falarsa.com",
                "NOMBRE" => "FALAR S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "FALAR S.A.",
                "SO" => 1400000008,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "atencionalcliente@falarsa.com",
                "NOMBRE" => "FALAR S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "FALAR S.A.",
                "SO" => 1400000008,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "duvalblanco@icloud.com",
                "NOMBRE" => "RICKISLAND S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "RICKISLAND S.A.",
                "SO" => 1400000010,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "emiliano@macri.com.uy",
                "NOMBRE" => "MARIO C. MACRI S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MARIO C. MACRI S.A.",
                "SO" => 1400000011,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "isabel.nievas@macri.com.uy",
                "NOMBRE" => "MARIO C. MACRI S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MARIO C. MACRI S.A.",
                "SO" => 1400000011,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "silvana.defaccio@macri.com.uy",
                "NOMBRE" => "MARIO C. MACRI S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MARIO C. MACRI S.A.",
                "SO" => 1400000011,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "lisandro@macri.com.uy",
                "NOMBRE" => "STARMAC S.A",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "STARMAC S.A",
                "SO" => 1400000012,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "analy.perez@macri.com.uy",
                "NOMBRE" => "STARMAC S.A",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "STARMAC S.A",
                "SO" => 1400000012,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "alejandra.ferreira@macri.com.uy",
                "NOMBRE" => "STARMAC S.A",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "STARMAC S.A",
                "SO" => 1400000012,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "carol.mello@macri.uy",
                "NOMBRE" => "STARMAC S.A",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "STARMAC S.A",
                "SO" => 1400000012,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mmmendezp@gmail.com",
                "NOMBRE" => "MENPI S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MENPI S.A.",
                "SO" => 1400000016,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "lorena.menpi@gmail.com",
                "NOMBRE" => "MENPI S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MENPI S.A.",
                "SO" => 1400000016,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "andrea.menpi@gmail.com",
                "NOMBRE" => "MENPI S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MENPI S.A.",
                "SO" => 1400000016,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mazzizssj4@hotmail.com",
                "NOMBRE" => "LANIBAN S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LANIBAN S.A.",
                "SO" => 1400000017,
                "PAIS" => "UR",
                "ROL" => "Cliente",
                "Column8" => "Approver MF"
            ],
            [
                "MAIL" => "robertlatalladatito@gmail.com",
                "NOMBRE" => "LANIBAN S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LANIBAN S.A.",
                "SO" => 1400000017,
                "PAIS" => "UR",
                "ROL" => "Cliente",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "casafermar@adinet.com.uy",
                "NOMBRE" => "LANIBAN S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LANIBAN S.A.",
                "SO" => 1400000017,
                "PAIS" => "UR",
                "ROL" => "Cliente",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "robertoboero@gmail.com",
                "NOMBRE" => "LABIGOLD S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LABIGOLD S.A.",
                "SO" => 1400000018,
                "PAIS" => "UR",
                "ROL" => "Cliente",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "labigold.deposito@gmail.com",
                "NOMBRE" => "LABIGOLD S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LABIGOLD S.A.",
                "SO" => 1400000018,
                "PAIS" => "UR",
                "ROL" => "Cliente",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "labigold.alinson@gmail.com",
                "NOMBRE" => "LABIGOLD S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LABIGOLD S.A.",
                "SO" => 1400000018,
                "PAIS" => "UR",
                "ROL" => "Cliente",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "adriangiannotti@hotmail.com",
                "NOMBRE" => "LIDERBLEX SA",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LIDERBLEX SA",
                "SO" => 1400000020,
                "PAIS" => "UR",
                "ROL" => "Cliente",
                "Column8" => "Approver MB"
            ],
            [
                "MAIL" => "logistica@giannotti.com.uy",
                "NOMBRE" => "LIDERBLEX SA",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "LIDERBLEX SA",
                "SO" => 1400000020,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "monacodeportes@hotmail.com",
                "NOMBRE" => "HOFLAND S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "HOFLAND S.A.",
                "SO" => 1400000021,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "monacodeportes@hotmail.com",
                "NOMBRE" => "ATLHETIC SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "ATLHETIC SRL",
                "SO" => 1400000023,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "monacodeportes@hotmail.com",
                "NOMBRE" => "MONACO SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "MONACO SRL",
                "SO" => 1400000003,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "monacotbo@gmail.com",
                "NOMBRE" => "HOFLAND S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "HOFLAND S.A.",
                "SO" => 1400000021,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "monacotbo@gmail.com",
                "NOMBRE" => "ATLHETIC SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "ATLHETIC SRL",
                "SO" => 1400000023,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "monacotbo@gmail.com",
                "NOMBRE" => "MONACO SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "MONACO SRL",
                "SO" => 1400000003,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "soledadezquerra@gmail.com",
                "NOMBRE" => "HOFLAND S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "HOFLAND S.A.",
                "SO" => 1400000021,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "soledadezquerra@gmail.com",
                "NOMBRE" => "ATLHETIC SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "ATLHETIC SRL",
                "SO" => 1400000023,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "soledadezquerra@gmail.com",
                "NOMBRE" => "MONACO SRL",
                "APELLIDO" => "SRL",
                "NOMBRE CLIENTE" => "MONACO SRL",
                "SO" => 1400000003,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "luislagioia@gmail.com",
                "NOMBRE" => "GOAL SPORT S.A",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "GOAL SPORT S.A",
                "SO" => 1400000022,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "corner.sport@hotmail.com",
                "NOMBRE" => "GOAL SPORT S.A",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "GOAL SPORT S.A",
                "SO" => 1400000022,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "producto@sportline.com.uy",
                "NOMBRE" => "NSP",
                "APELLIDO" => "NSP",
                "NOMBRE CLIENTE" => "NSP",
                "SO" => 1400000435,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "producto@sportline.com.uy",
                "NOMBRE" => "MULTIBRAND",
                "APELLIDO" => "MULTIBRAND",
                "NOMBRE CLIENTE" => "MULTIBRAND",
                "SO" => 1400000436,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "administracion1@eltonir.com.uy",
                "NOMBRE" => "MULTIBRAND",
                "APELLIDO" => "MULTIBRAND",
                "NOMBRE CLIENTE" => "MULTIBRAND",
                "SO" => 1400000436,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "administracion1@eltonir.com.uy",
                "NOMBRE" => "GLOBAL",
                "APELLIDO" => "GLOBAL",
                "NOMBRE CLIENTE" => "GLOBAL",
                "SO" => 1400000437,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "administracion1@eltonir.com.uy",
                "NOMBRE" => "ZOOKO",
                "APELLIDO" => "ZOOKO",
                "NOMBRE CLIENTE" => "ZOOKO",
                "SO" => 1400000438,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "administracion1@eltonir.com.uy",
                "NOMBRE" => "MILBUS SA",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MILBUS SA",
                "SO" => 1400000015,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "sports@adinet.com.uy",
                "NOMBRE" => "GLOBAL",
                "APELLIDO" => "GLOBAL",
                "NOMBRE CLIENTE" => "GLOBAL",
                "SO" => 1400000437,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "sports@adinet.com.uy",
                "NOMBRE" => "ZOOKO",
                "APELLIDO" => "ZOOKO",
                "NOMBRE CLIENTE" => "ZOOKO",
                "SO" => 1400000438,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "sports@adinet.com.uy",
                "NOMBRE" => "MILBUS SA",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "MILBUS SA",
                "SO" => 1400000015,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "estanislao.pinero@southbay.com.ar",
                "NOMBRE" => "NDDC",
                "APELLIDO" => "NDDC",
                "NOMBRE CLIENTE" => "NDDC",
                "SO" => 1400000441,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "sebastian.fernandez@southbay.com.ar",
                "NOMBRE" => "NDDC",
                "APELLIDO" => "NDDC",
                "NOMBRE CLIENTE" => "NDDC",
                "SO" => 1400000441,
                "PAIS" => "UR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "ymedina@grupodabra.com.ar",
                "NOMBRE" => "Yesica",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "DABRA S.A.",
                "SO" => 1400000027,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "ymedina@grupodabra.com.ar",
                "NOMBRE" => "Yesica",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "MOOV",
                "SO" => 1400000141,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "ymedina@grupodabra.com.ar",
                "NOMBRE" => "Yesica",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "DEXTER",
                "SO" => 1400000142,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "ymedina@grupodabra.com.ar",
                "NOMBRE" => "Yesica",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "STOCKCENTER",
                "SO" => 1400000143,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mrestivo@grupodabra.com.ar",
                "NOMBRE" => "Maria Andrea",
                "APELLIDO" => "Restivo",
                "NOMBRE CLIENTE" => "DABRA S.A.",
                "SO" => 1400000027,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mrestivo@grupodabra.com.ar",
                "NOMBRE" => "Maria Andrea",
                "APELLIDO" => "Restivo",
                "NOMBRE CLIENTE" => "MOOV",
                "SO" => 1400000141,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mrestivo@grupodabra.com.ar",
                "NOMBRE" => "Maria Andrea",
                "APELLIDO" => "Restivo",
                "NOMBRE CLIENTE" => "DEXTER",
                "SO" => 1400000142,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mrestivo@grupodabra.com.ar",
                "NOMBRE" => "Maria Andrea",
                "APELLIDO" => "Restivo",
                "NOMBRE CLIENTE" => "STOCKCENTER",
                "SO" => 1400000143,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "depositodefallas2011@hotmail.com",
                "NOMBRE" => "Marcos",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "CAMARINAS S.A.",
                "SO" => 1400000119,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "depositodefallas2011@hotmail.com",
                "NOMBRE" => "Marcos",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "VIMIANZO S.A.",
                "SO" => 1400000125,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "depositodefallas2011@hotmail.com",
                "NOMBRE" => "Marcos",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "FITZROVIA",
                "SO" => 1400000189,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "depositodefallas2011@hotmail.com",
                "NOMBRE" => "Marcos",
                "APELLIDO" => "Medina",
                "NOMBRE CLIENTE" => "LORIA 2",
                "SO" => 1400000190,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "robertobustos@vaypol.com.ar ",
                "NOMBRE" => "Roberto",
                "APELLIDO" => "Bustos",
                "NOMBRE CLIENTE" => "MABEL SALINAS S.A.",
                "SO" => 1400000120,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "robertobustos@vaypol.com.ar ",
                "NOMBRE" => "Roberto",
                "APELLIDO" => "Bustos",
                "NOMBRE CLIENTE" => "MABEL SALINAS BETTER SG 2.0 SAN MAR",
                "SO" => 1400000191,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "robertobustos@vaypol.com.ar ",
                "NOMBRE" => "Roberto",
                "APELLIDO" => "Bustos",
                "NOMBRE CLIENTE" => "MABEL SALINAS C. S. NSW",
                "SO" => 1400000192,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Garantias@dash-deportes.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Velardez",
                "NOMBRE CLIENTE" => "ANNEX S.A.",
                "SO" => 1400000126,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Devoluciones@ferreirasport.com",
                "NOMBRE" => "Marcela",
                "APELLIDO" => "Sevalt",
                "NOMBRE CLIENTE" => "FERREIRA SEPTIMO",
                "SO" => 1400000148,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Devoluciones@ferreirasport.com",
                "NOMBRE" => "Marcela",
                "APELLIDO" => "Sevalt",
                "NOMBRE CLIENTE" => "FERREIRA SPORT S.A",
                "SO" => 1400000128,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sevensportgroup.com.ar",
                "NOMBRE" => "Ezequiel",
                "APELLIDO" => "Vazquez",
                "NOMBRE CLIENTE" => "FACTORY SPORT S.A.",
                "SO" => 1400000129,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sevensportgroup.com.ar",
                "NOMBRE" => "Ezequiel",
                "APELLIDO" => "Vazquez",
                "NOMBRE CLIENTE" => "TOP SPORT S.A",
                "SO" => 1400000130,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sevensportgroup.com.ar",
                "NOMBRE" => "Ezequiel",
                "APELLIDO" => "Vazquez",
                "NOMBRE CLIENTE" => "FORTALEZA EXTREMA S.A.",
                "SO" => 1400000131,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sevensportgroup.com.ar",
                "NOMBRE" => "Ezequiel",
                "APELLIDO" => "Vazquez",
                "NOMBRE CLIENTE" => "FACTORY BETTER",
                "SO" => 1400000153,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sevensportgroup.com.ar",
                "NOMBRE" => "Ezequiel",
                "APELLIDO" => "Vazquez",
                "NOMBRE CLIENTE" => "FORTALEZ EXTREMA S.A CHELSEA",
                "SO" => 1400000204,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "rodrigo.chazarreta@lacelada.com",
                "NOMBRE" => "Rodrigo",
                "APELLIDO" => "Chazarreta",
                "NOMBRE CLIENTE" => "LA CELADA JJ DEPORTES",
                "SO" => 1400000132,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "rodrigo.chazarreta@lacelada.com",
                "NOMBRE" => "Rodrigo",
                "APELLIDO" => "Chazarreta",
                "NOMBRE CLIENTE" => "LA CELADA ON SPORTS",
                "SO" => 1400000154,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "rodrigo.chazarreta@lacelada.com",
                "NOMBRE" => "Rodrigo",
                "APELLIDO" => "Chazarreta",
                "NOMBRE CLIENTE" => "LA CELADA IN STORE",
                "SO" => 1400000155,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "deposito@totalsport.com.ar",
                "NOMBRE" => "Diego",
                "APELLIDO" => "Rodriguez",
                "NOMBRE CLIENTE" => "TOTAL SPORT SRL",
                "SO" => 1400000133,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "yrodriguez@coppel.com.ar",
                "NOMBRE" => "Yesica",
                "APELLIDO" => "Rodriguez",
                "NOMBRE CLIENTE" => "COPPEL S.A.",
                "SO" => 1400000134,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "blopez@coppel.com.ar",
                "NOMBRE" => "Blacida",
                "APELLIDO" => "Lopez",
                "NOMBRE CLIENTE" => "COPPEL S.A.",
                "SO" => 1400000134,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "forsaria@coppel.com.ar",
                "NOMBRE" => "Franco",
                "APELLIDO" => "Orsaria",
                "NOMBRE CLIENTE" => "COPPEL S.A.",
                "SO" => 1400000134,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Julieta.ramirez@woodooskateboards.com",
                "NOMBRE" => "Julieta",
                "APELLIDO" => "Ramirez",
                "NOMBRE CLIENTE" => "DRIFTER S.A.",
                "SO" => 1400000135,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "compostura@opensports.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Damianovich",
                "NOMBRE CLIENTE" => "MDQ R&B",
                "SO" => 1400000144,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "compostura@opensports.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Damianovich",
                "NOMBRE CLIENTE" => "STADIO UNO TRIP",
                "SO" => 1400000145,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "compostura@opensports.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Damianovich",
                "NOMBRE CLIENTE" => "STADIO UNO OPEN",
                "SO" => 1400000127,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "compostura@opensports.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Damianovich",
                "NOMBRE CLIENTE" => "MDQ LE SPORT S.A.",
                "SO" => 1400000201,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "compostura@opensports.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Damianovich",
                "NOMBRE CLIENTE" => "MDQ LE SPORT S.A. GuEMES AS",
                "SO" => 1400000202,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "compostura@opensports.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Damianovich",
                "NOMBRE CLIENTE" => "MDQ LE SPORT S.A. L1",
                "SO" => 1400000203,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "noelia.duberti@luquin.com.ar",
                "NOMBRE" => "Noelia",
                "APELLIDO" => "Duberti",
                "NOMBRE CLIENTE" => "ANTONIO LUQUIN S.A.",
                "SO" => 1400000146,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "noelia.duberti@luquin.com.ar",
                "NOMBRE" => "Noelia",
                "APELLIDO" => "Duberti",
                "NOMBRE CLIENTE" => "LUQUIN BEST AS",
                "SO" => 1400000147,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "noelia.duberti@luquin.com.ar",
                "NOMBRE" => "Noelia",
                "APELLIDO" => "Duberti",
                "NOMBRE CLIENTE" => "ANTONIO LUQUIN S.A. NS Tucuman",
                "SO" => 1400000193,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "noelia.duberti@luquin.com.ar",
                "NOMBRE" => "Noelia",
                "APELLIDO" => "Duberti",
                "NOMBRE CLIENTE" => "ANTONIO LUQUIN S.A.",
                "SO" => 1400000416,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "noelia.duberti@luquin.com.ar",
                "NOMBRE" => "Noelia",
                "APELLIDO" => "Duberti",
                "NOMBRE CLIENTE" => "ANTONIO LUQUIN S.A.",
                "SO" => 1400000424,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "noelia.duberti@luquin.com.ar",
                "NOMBRE" => "Noelia",
                "APELLIDO" => "Duberti",
                "NOMBRE CLIENTE" => "ANTONIO LUQUIN S.A.",
                "SO" => 1400000425,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "noelia.duberti@luquin.com.ar",
                "NOMBRE" => "Noelia",
                "APELLIDO" => "Duberti",
                "NOMBRE CLIENTE" => "ANTONIO LUQUIN S.A.",
                "SO" => 1400000426,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "garantias@authogar.com",
                "NOMBRE" => "Marina",
                "APELLIDO" => "Muelas",
                "NOMBRE CLIENTE" => "CASIMIRO FELIX TOYOS",
                "SO" => 1400000149,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "garantias@authogar.com",
                "NOMBRE" => "Marina",
                "APELLIDO" => "Muelas",
                "NOMBRE CLIENTE" => "CASIMIRO FELIX TOYOS SANTA OLA",
                "SO" => 1400000150,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "soleallende.nike@gmail.com",
                "NOMBRE" => "Zaira Soledad",
                "APELLIDO" => "Allende",
                "NOMBRE CLIENTE" => "DEPORTES PEnA CORDOBA",
                "SO" => 1400000151,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "soleallende.nike@gmail.com",
                "NOMBRE" => "Zaira Soledad",
                "APELLIDO" => "Allende",
                "NOMBRE CLIENTE" => "DEPORTES PEnA SANTA FE",
                "SO" => 1400000152,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "calzado@ferracioli.com.ar",
                "NOMBRE" => "Rodrigo angel",
                "APELLIDO" => "Giacaman",
                "NOMBRE CLIENTE" => "CASA FERRACIOLI",
                "SO" => 1400000156,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "richieri@ferracioli.com.ar",
                "NOMBRE" => "Rodrigo angel",
                "APELLIDO" => "Giacaman",
                "NOMBRE CLIENTE" => "CASA FERRACIOLI",
                "SO" => 1400000156,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "administracion@hanssachs.com.ar",
                "NOMBRE" => "Oscar",
                "APELLIDO" => "Manduca",
                "NOMBRE CLIENTE" => "HANSSACHS",
                "SO" => 1400000157,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "administracion@hanssachs.com.ar",
                "NOMBRE" => "Oscar",
                "APELLIDO" => "Manduca",
                "NOMBRE CLIENTE" => "VIEGAS",
                "SO" => 1400000158,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "manuel@totalsport.com.ar",
                "NOMBRE" => "Manuel",
                "APELLIDO" => "Gonzalez Vidal",
                "NOMBRE CLIENTE" => "TOTAL SPORT 6DIEZ",
                "SO" => 1400000162,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "reclamosnewsport@gmail.com",
                "NOMBRE" => "Julian",
                "APELLIDO" => "Lopez",
                "NOMBRE CLIENTE" => "ADOLFO ZAKIAN S.A.",
                "SO" => 1400000164,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "reclamosnewsport@gmail.com",
                "NOMBRE" => "Julian",
                "APELLIDO" => "Lopez",
                "NOMBRE CLIENTE" => "FUENCARRAL",
                "SO" => 1400000165,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "reclamosnewsport@gmail.com",
                "NOMBRE" => "Julian",
                "APELLIDO" => "Lopez",
                "NOMBRE CLIENTE" => "ZETA 4 SB",
                "SO" => 1400000205,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "reclamosnewsport@gmail.com",
                "NOMBRE" => "Julian",
                "APELLIDO" => "Lopez",
                "NOMBRE CLIENTE" => "ADOLFO ZAKIAN S.A. TEMPLO FUTBOL",
                "SO" => 1400000206,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "reclamosnewsport@gmail.com",
                "NOMBRE" => "Julian",
                "APELLIDO" => "Lopez",
                "NOMBRE CLIENTE" => "ADOLFO ZAKIAN S.A.",
                "SO" => 1400000417,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS DIONYSOS  BS AS",
                "APELLIDO" => "GRAELLS DIONYSOS  BS AS",
                "NOMBRE CLIENTE" => "GRAELLS DIONYSOS  BS AS",
                "SO" => 1400000166,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "DIONYSOS",
                "APELLIDO" => "DIONYSOS",
                "NOMBRE CLIENTE" => "DIONYSOS",
                "SO" => 1400000418,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.  SPORT 78",
                "APELLIDO" => "GRAELLS NELSON A.  SPORT 78",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.  SPORT 78",
                "SO" => 1400000419,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.  SPORT 78",
                "APELLIDO" => "GRAELLS NELSON A.  SPORT 78",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.  SPORT 78",
                "SO" => 1400000420,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.  SPORT 78",
                "APELLIDO" => "GRAELLS NELSON A.  SPORT 78",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.  SPORT 78",
                "SO" => 1400000427,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.  SPORT 78",
                "APELLIDO" => "GRAELLS NELSON A.  SPORT 78",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.  SPORT 78",
                "SO" => 1400000428,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "BLAST AS D",
                "APELLIDO" => "BLAST AS D",
                "NOMBRE CLIENTE" => "BLAST AS D",
                "SO" => 1400000429,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.  SPORT 78",
                "APELLIDO" => "GRAELLS NELSON A.  SPORT 78",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.  SPORT 78",
                "SO" => 1400000421,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A. Nike Shop",
                "APELLIDO" => "GRAELLS NELSON A. Nike Shop",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A. Nike Shop",
                "SO" => 1400000194,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.NS ALTO ROSARIO",
                "APELLIDO" => "GRAELLS NELSON A.NS ALTO ROSARIO",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.NS ALTO ROSARIO",
                "SO" => 1400000195,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "DIONYSOS",
                "APELLIDO" => "DIONYSOS",
                "NOMBRE CLIENTE" => "DIONYSOS",
                "SO" => 1400000207,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "FLUID",
                "APELLIDO" => "FLUID",
                "NOMBRE CLIENTE" => "FLUID",
                "SO" => 1400000208,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.L2 PORTAL SHOPP",
                "APELLIDO" => "GRAELLS NELSON A.L2 PORTAL SHOPP",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.L2 PORTAL SHOPP",
                "SO" => 1400000209,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devoluciones@sport78.com.ar",
                "NOMBRE" => "GRAELLS NELSON A.BLAST AS ALTO ROS",
                "APELLIDO" => "GRAELLS NELSON A.BLAST AS ALTO ROS",
                "NOMBRE CLIENTE" => "GRAELLS NELSON A.BLAST AS ALTO ROS",
                "SO" => 1400000210,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "andrea.piunti@solodeportes.com",
                "NOMBRE" => "Andrea",
                "APELLIDO" => "Piunti",
                "NOMBRE CLIENTE" => "BLOISE Hnos.Soc.de H.",
                "SO" => 1400000167,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "fallas@rossettideportes.com",
                "NOMBRE" => "Alejandro",
                "APELLIDO" => "Cisana",
                "NOMBRE CLIENTE" => "CARLOS ROSSETTI S.A. - NS - NC",
                "SO" => 1400000196,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "fallas@rossettideportes.com",
                "NOMBRE" => "Alejandro",
                "APELLIDO" => "Cisana",
                "NOMBRE CLIENTE" => "CARLOS ROSSETTI S.A.",
                "SO" => 1400000168,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "fallas@rossettideportes.com",
                "NOMBRE" => "Alejandro",
                "APELLIDO" => "Cisana",
                "NOMBRE CLIENTE" => "CARLOS ROSSETTI S.A. AS - V.MARIA",
                "SO" => 1400000215,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "fallas@rossettideportes.com",
                "NOMBRE" => "Alejandro",
                "APELLIDO" => "Cisana",
                "NOMBRE CLIENTE" => "CARLOS ROSSETTI S.A.",
                "SO" => 1400000422,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "fallas@rossettideportes.com",
                "NOMBRE" => "Alejandro",
                "APELLIDO" => "Cisana",
                "NOMBRE CLIENTE" => "CARLOS ROSSETTI S.A.",
                "SO" => 1400000423,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "emiliano.a@megasp.com.ar",
                "NOMBRE" => "Emiliano",
                "APELLIDO" => "Awad",
                "NOMBRE CLIENTE" => "CARJUL S.A.",
                "SO" => 1400000170,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "emiliano.a@megasp.com.ar",
                "NOMBRE" => "Emiliano",
                "APELLIDO" => "Awad",
                "NOMBRE CLIENTE" => "CARJUL SA BETTER",
                "SO" => 1400000171,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "emiliano.a@megasp.com.ar",
                "NOMBRE" => "Emiliano",
                "APELLIDO" => "Awad",
                "NOMBRE CLIENTE" => "SPORTS LIFE SA",
                "SO" => 1400000185,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mvico@deporjean.com",
                "NOMBRE" => "Matias",
                "APELLIDO" => "Vico",
                "NOMBRE CLIENTE" => "DEPOR JEAN DIGITAL",
                "SO" => 1400000174,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mvico@deporjean.com",
                "NOMBRE" => "Matias",
                "APELLIDO" => "Vico",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 ESQUINA STA FE",
                "SO" => 1400000175,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mvico@deporjean.com",
                "NOMBRE" => "Matias",
                "APELLIDO" => "Vico",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 PARANA",
                "SO" => 1400000176,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mvico@deporjean.com",
                "NOMBRE" => "Matias",
                "APELLIDO" => "Vico",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 SANTA FE",
                "SO" => 1400000177,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mvico@deporjean.com",
                "NOMBRE" => "Matias",
                "APELLIDO" => "Vico",
                "NOMBRE CLIENTE" => "DEPOR JEAN RUSH TOWN",
                "SO" => 1400000178,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "mvico@deporjean.com",
                "NOMBRE" => "Matias",
                "APELLIDO" => "Vico",
                "NOMBRE CLIENTE" => "DEPOR JEAN S.A.",
                "SO" => 1400000179,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "miribas@deporjean.com",
                "NOMBRE" => "Marcelo",
                "APELLIDO" => "Iribas",
                "NOMBRE CLIENTE" => "DEPOR JEAN DIGITAL",
                "SO" => 1400000174,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "miribas@deporjean.com",
                "NOMBRE" => "Marcelo",
                "APELLIDO" => "Iribas",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 ESQUINA STA FE",
                "SO" => 1400000175,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "miribas@deporjean.com",
                "NOMBRE" => "Marcelo",
                "APELLIDO" => "Iribas",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 PARANA",
                "SO" => 1400000176,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "miribas@deporjean.com",
                "NOMBRE" => "Marcelo",
                "APELLIDO" => "Iribas",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 SANTA FE",
                "SO" => 1400000177,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "miribas@deporjean.com",
                "NOMBRE" => "Marcelo",
                "APELLIDO" => "Iribas",
                "NOMBRE CLIENTE" => "DEPOR JEAN RUSH TOWN",
                "SO" => 1400000178,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "miribas@deporjean.com",
                "NOMBRE" => "Marcelo",
                "APELLIDO" => "Iribas",
                "NOMBRE CLIENTE" => "DEPOR JEAN S.A.",
                "SO" => 1400000179,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devolucionafabrica@deporjean.com",
                "NOMBRE" => "DEPOR JEAN DIGITAL",
                "APELLIDO" => "DEPOR JEAN DIGITAL",
                "NOMBRE CLIENTE" => "DEPOR JEAN DIGITAL",
                "SO" => 1400000174,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devolucionafabrica@deporjean.com",
                "NOMBRE" => "DEPOR JEAN L2 ESQUINA STA FE",
                "APELLIDO" => "DEPOR JEAN L2 ESQUINA STA FE",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 ESQUINA STA FE",
                "SO" => 1400000175,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devolucionafabrica@deporjean.com",
                "NOMBRE" => "DEPOR JEAN L2 PARANA",
                "APELLIDO" => "DEPOR JEAN L2 PARANA",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 PARANA",
                "SO" => 1400000176,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devolucionafabrica@deporjean.com",
                "NOMBRE" => "DEPOR JEAN L2 SANTA FE",
                "APELLIDO" => "DEPOR JEAN L2 SANTA FE",
                "NOMBRE CLIENTE" => "DEPOR JEAN L2 SANTA FE",
                "SO" => 1400000177,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devolucionafabrica@deporjean.com",
                "NOMBRE" => "DEPOR JEAN RUSH TOWN",
                "APELLIDO" => "DEPOR JEAN RUSH TOWN",
                "NOMBRE CLIENTE" => "DEPOR JEAN RUSH TOWN",
                "SO" => 1400000178,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "devolucionafabrica@deporjean.com",
                "NOMBRE" => "DEPOR JEAN S.A.",
                "APELLIDO" => "S.A.",
                "NOMBRE CLIENTE" => "DEPOR JEAN S.A.",
                "SO" => 1400000179,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "francisco.garcia@pacogarcia.com.ar",
                "NOMBRE" => "Francisco",
                "APELLIDO" => "Garcia",
                "NOMBRE CLIENTE" => "PACO GARCIA S.A.",
                "SO" => 1400000182,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "francisco.garcia@pacogarcia.com.ar",
                "NOMBRE" => "Francisco",
                "APELLIDO" => "Garcia",
                "NOMBRE CLIENTE" => "PACO GARCIA SA L2",
                "SO" => 1400000183,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "carlos.parodi@valenet.com.ar",
                "NOMBRE" => "Carlos",
                "APELLIDO" => "Parodi",
                "NOMBRE CLIENTE" => "TESI S.A.",
                "SO" => 1400000186,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "carlos.parodi@valenet.com.ar",
                "NOMBRE" => "Carlos",
                "APELLIDO" => "Parodi",
                "NOMBRE CLIENTE" => "TESI S.A. MENDOZA",
                "SO" => 1400000187,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "carlos.parodi@valenet.com.ar",
                "NOMBRE" => "Carlos",
                "APELLIDO" => "Parodi",
                "NOMBRE CLIENTE" => "TESI S.A. SAN JUAN",
                "SO" => 1400000188,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Garantias@dash-deportes.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Velardez",
                "NOMBRE CLIENTE" => "AMI MUSIC S.A.",
                "SO" => 1400000197,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Garantias@dash-deportes.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Velardez",
                "NOMBRE CLIENTE" => "ESSENTIAL S.A.",
                "SO" => 1400000198,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Garantias@dash-deportes.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Velardez",
                "NOMBRE CLIENTE" => "ESSENTIAL S.A. MARK",
                "SO" => 1400000199,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Garantias@dash-deportes.com.ar",
                "NOMBRE" => "Pablo",
                "APELLIDO" => "Velardez",
                "NOMBRE CLIENTE" => "ANNEX S.A. CSW",
                "SO" => 1400000200,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "reparaciones@sportline.com.ar",
                "NOMBRE" => "Diego",
                "APELLIDO" => "Montiel",
                "NOMBRE CLIENTE" => "ISRAEL FELER S.A.",
                "SO" => 1400000211,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "garantias@showsport.com.ar",
                "NOMBRE" => "Miguel Angel",
                "APELLIDO" => "Sanchez",
                "NOMBRE CLIENTE" => "KADIMA S.A.",
                "SO" => 1400000212,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "garantias@mateu.com.ar",
                "NOMBRE" => "Fabian",
                "APELLIDO" => "Guida",
                "NOMBRE CLIENTE" => "MATEU SPORTS",
                "SO" => 1400000213,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "garantias@mateu.com.ar",
                "NOMBRE" => "Fabian",
                "APELLIDO" => "Guida",
                "NOMBRE CLIENTE" => "MATEU SPORTS AURELIUS",
                "SO" => 1400000214,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "k.superfutbol@gmail.com",
                "NOMBRE" => "Kevin",
                "APELLIDO" => "Echt",
                "NOMBRE CLIENTE" => "SUPERFUTBOL SRL",
                "SO" => 1400000216,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ],
            [
                "MAIL" => "Julian.Luongo@torneos.com",
                "NOMBRE" => "Julian",
                "APELLIDO" => "Luongo",
                "NOMBRE CLIENTE" => "TORNEOS Y COMPETENCIAS S.A.",
                "SO" => 1400000217,
                "PAIS" => "AR",
                "ROL" => "Cliente"
            ]
        ];
    }

}
