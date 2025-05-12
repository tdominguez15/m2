<?php
/**
 * ViewModel para la página de aterrizaje (landing) personalizada.
 *
 * @package Southbay\CustomCustomer\ViewModel
 */

namespace Southbay\CustomCustomer\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\CustomCustomer\Model\CustomerConfigRepository;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory as ConfigStoreCollectionFactory;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class LandingViewModel implements ArgumentInterface
{
    /**
     * @var CustomerConfigRepository
     */
    protected $customerConfigRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigStoreCollectionFactory
     */
    protected $configStoreCollectionFactory;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformationAcquirerInterface;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Constructor.
     *
     * @param CustomerConfigRepository $customerConfigRepository
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ConfigStoreCollectionFactory $configStoreCollectionFactory
     * @param CountryInformationAcquirerInterface $countryInformationAcquirerInterface
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     */
    public function __construct(
        CustomerConfigRepository            $customerConfigRepository,
        Session                             $customerSession,
        StoreManagerInterface               $storeManager,
        ConfigStoreCollectionFactory        $configStoreCollectionFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirerInterface,
        ScopeConfigInterface                $scopeConfig,
        DateTime                            $dateTime
    )
    {
        $this->customerConfigRepository = $customerConfigRepository;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->configStoreCollectionFactory = $configStoreCollectionFactory;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
    }

    /**
     * Obtiene la URL de la tienda por su código.
     *
     * @param string $storeCode
     * @return string
     */
    public function getStoreUrlByCode($storeCode,$functionCode)
    {
        try {
            $store = $this->storeManager->getStore($storeCode);
            if ($store->getId()) {
                if($functionCode == ConfigStoreRepositoryInterface::SOUTHBAY_RTV){
                    return $store->getBaseUrl() . "customer/account";
                }
                else {
                    return $store->getBaseUrl() . "landing/soldto";
                }
            } else {
                return 'La tienda con el código ' . $storeCode . ' no fue encontrada.';
            }
        } catch (\Exception $e) {
            return 'Error al obtener la URL de la tienda: ' . $e->getMessage();
        }
    }


    /**
     * Obtiene las configuraciones de las tiendas.
     *
     * @return array
     */
    public function getConfigStores()
    {

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $configStoreCollection
         */
        $configStoreCollection = $this->configStoreCollectionFactory->create();
        $arrayFormatted = [];
        $customerEmail = $this->customerSession->getCustomer()->getEmail();
        $customConfig = $this->customerConfigRepository->findByCustomerEmail($customerEmail);
        $functionCodes = [];
        $countryCodes = [];

        if (!is_null($customConfig)) {
            if (!empty($customConfig->getFunctionsCodes())) {
                $functionCodes = explode(',', $customConfig->getFunctionsCodes());
            }
            if (!empty($customConfig->getCountriesCodes())) {
                $countryCodes = explode(',', $customConfig->getCountriesCodes());
            }
        }

        foreach ($configStoreCollection as $configStore) {

            if (in_array($configStore->getFunctionCode(), $functionCodes) && in_array($configStore->getCountryCode(), $countryCodes) && $this->isButtonEnableAndInSchedule($configStore->getStoreCode())) {
                $arrayFormatted[$configStore->getSouthbayCountryCode()][] = [
                    'functionCode' => $configStore->getSouthbayFunctionCode(),
                    'storeCode' => $configStore->getSouthbayStoreCode(),
                    'url' => $this->getStoreUrlByCode($configStore->getSouthbayStoreCode(),$configStore->getFunctionCode()),
                ];
            }
        }
        $order = ['futures', 'at_once', 'rtv'];
        foreach ($arrayFormatted as &$stores) {
            usort($stores, function ($a, $b) use ($order) {
                $posA = array_search($a['functionCode'], $order);
                $posB = array_search($b['functionCode'], $order);
                return $posA - $posB;
            });
        }

        return $arrayFormatted;
    }


    /**
     * Obtiene el nombre del país por su código.
     *
     * @param string $countryCode
     * @param string $type
     * @return string|null
     */
    public function getCountryName($countryCode, $type = "local")
    {
        $countryName = null;
        try {
            $data = $this->countryInformationAcquirerInterface->getCountryInfo($countryCode);
            if ($type == "local") {
                $countryName = $data->getFullNameEnglish();
            }
        } catch (NoSuchEntityException $e) {
        }
        if (empty($countryName)) {
            if ($countryCode == 'AR') {
                $countryName = 'Argentina.';
            } elseif ($countryCode == 'UY') {
                $countryName = 'Uruguay.';
            }

        }
        return $countryName;
    }
    /**
     * Obtiene el valor de la configuración "Enable Menu Button" para un scope específico.
     *
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    public function isMenuButtonEnabled($scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'southbay_landing/general/enable_menu_button',
            $scope,
            $scopeId
        );
    }


    /**
     * Obtiene el valor de la configuración "Enable Menu Button" para un store view específico.
     *
     * @param int $storeId
     * @return bool
     */
    public function isMenuButtonEnabledForStore($storeId)
    {
        return $this->isMenuButtonEnabled(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
    /**
    * Verifica si el botón debe estar visible para una tienda específica.
    *
    * @param int $storeId
    * @return bool
    */
    public function isButtonEnableAndInSchedule($storeId)
    {
        $isEnabled = $this->isMenuButtonEnabledForStore($storeId);
        $isScheduleEnabled = $this->scopeConfig->isSetFlag('southbay_landing/general/enable_based_on_schedule', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $startTime = $this->scopeConfig->getValue('southbay_landing/general/start_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $endTime = $this->scopeConfig->getValue('southbay_landing/general/end_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

        if (!$isEnabled) {
            return false;
        }

        if ($isScheduleEnabled) {
            $currentTime = new \DateTime('now', new \DateTimeZone('UTC'));
            $currentTime->modify('-3 hours'); // para que tenga zona horaria de argentina

            $currentFormattedTime = $currentTime->format('H:i');
            $startTimeFormatted = \DateTime::createFromFormat('H:i', $startTime)->format('H:i');
            $endTimeFormatted = \DateTime::createFromFormat('H:i', $endTime)->format('H:i');
            return ($currentFormattedTime >= $startTimeFormatted && $currentFormattedTime <= $endTimeFormatted);        }

        return true;
    }


}
