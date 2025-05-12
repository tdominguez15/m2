<?php

namespace Southbay\CustomCustomer\ViewModel;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Model\CustomerConfigRepository;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory as ConfigStoreCollectionFactory;
use Southbay\CustomCustomer\Model\ResourceModel\SoldTo\Collection;
use Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory as SoldToCollectionFactory;
use Southbay\CustomCustomer\Model\SoldToRepository;
use Southbay\CustomCustomer\Model\ShipToRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Message\ManagerInterface;

class SoldToViewModel implements ArgumentInterface
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
     * @var SoldToRepository
     */
    protected $soldToRepository;

    /**
     * @var SoldToCollectionFactory
     */
    protected $soldToCollectionFactory;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressInterfaceFactory;

    /**
     * @var RegionInterfaceFactory
     */
    protected $regionInterfaceFactory;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var ShipToRepository
     */
    protected $shipToRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroup
     */
    protected $filterGroup;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * SoldToViewModel constructor.
     *
     * @param CustomerConfigRepository $customerConfigRepository
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ConfigStoreCollectionFactory $configStoreCollectionFactory
     * @param CountryInformationAcquirerInterface $countryInformationAcquirerInterface
     * @param SoldToRepository $soldToRepository
     * @param SoldToCollectionFactory $soldToCollectionFactory
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param RegionInterfaceFactory $regionInterfaceFactory
     * @param AddressFactory $addressFactory
     * @param ShipToRepository $shipToRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroup $filterGroup
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CustomerConfigRepository            $customerConfigRepository,
        Session                             $customerSession,
        StoreManagerInterface               $storeManager,
        ConfigStoreCollectionFactory        $configStoreCollectionFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirerInterface,
        SoldToRepository                    $soldToRepository,
        SoldToCollectionFactory             $soldToCollectionFactory,
        AddressInterfaceFactory             $addressInterfaceFactory,
        RegionInterfaceFactory              $regionInterfaceFactory,
        AddressFactory                      $addressFactory,
        ShipToRepository                    $shipToRepository,
        AddressRepositoryInterface          $addressRepository,
        SearchCriteriaBuilder               $searchCriteriaBuilder,
        FilterBuilder                       $filterBuilder,
        FilterGroup                         $filterGroup,
        ManagerInterface                    $messageManager
    )
    {
        $this->customerConfigRepository = $customerConfigRepository;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->configStoreCollectionFactory = $configStoreCollectionFactory;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->soldToRepository = $soldToRepository;
        $this->soldToCollectionFactory = $soldToCollectionFactory;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->regionInterfaceFactory = $regionInterfaceFactory;
        $this->addressFactory = $addressFactory;
        $this->shipToRepository = $shipToRepository;
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
        $this->messageManager = $messageManager;
    }

    /**
     * Get Sold To collection
     *
     * @return Collection
     */
    public function getSoldTo(): Collection
    {
        $customerConfig = $this->getCustomerConfigByEmail();
        $soldsToIds = $customerConfig->getSoldToIds();
        $soldsTo = (!empty($soldsToIds)) ? explode(',',$soldsToIds) : "" ;
        $storeId = $this->storeManager->getStore()->getId();
        $configStore =  $this->configStoreCollectionFactory->create()->addFieldToFilter('southbay_store_code', ['eq' => $storeId]);
        $storeCountry =  $configStore->getFirstItem()->getSouthbayCountryCode();
        $soldToCollection = $this->soldToCollectionFactory->create();
        $soldToCollection->addFieldToFilter('southbay_sold_to_country_code', ['eq' => $storeCountry]);
        $soldToCollection->addFieldToFilter('southbay_sold_to_id', ['in' => $soldsTo]);
        $soldToCollection->addFieldToFilter('southbay_sold_to_locked', ['eq' => false]);
        $soldToCollection->addFieldToFilter('southbay_sold_to_is_active', ['eq' => true]);
        return $soldToCollection;
    }

    /**
     * Get Sold To collection
     *
     * @return \Southbay\CustomCustomer\Model\SoldTo
     */
    public function getSoldToFromSession()
    {
        $this->customerSession->start();
        $sold_to_id = $this->customerSession->getSoldtoId();

        $soldToCollection = $this->soldToCollectionFactory->create();
        $soldToCollection->addFieldToFilter('southbay_sold_to_id', ['eq' => $sold_to_id]);
        $soldToCollection->load();

        if ($soldToCollection->count() > 0) {
            return $soldToCollection->getFirstItem();
        }
        return null;
    }

    /**
     * Get Customer Configuration by Email
     *
     * @return \Southbay\CustomCustomer\Model\CustomerConfig
     */
    public function getCustomerConfigByEmail()
    {
        $customerEmail = $this->customerSession->getCustomer()->getEmail();
        return $this->customerConfigRepository->findByCustomerEmail($customerEmail);
    }

    /**
     * Create Shipping Address from Data
     *
     * @param int $soldToId
     * @return bool
     */
    public function createShippingAddressFromData($soldToId)
    {
        $soldTo = $this->soldToRepository->getById($soldToId);
        $shipTo = $this->shipToRepository->getByCustomerCode($soldTo->getCustomerCode());
        $customerId = $this->customerSession->getCustomerId();

        $shipToIds = [];
        if(empty($shipTo)){
            $this->messageManager->addErrorMessage(__('No hay Destinos De Mercaderia asignados a este solicitante, consulte a su representante.'));
            return false;
        }
        foreach ($shipTo as $shipToAddress) {
            $shipToIds[] = $shipToAddress->getId();
        }
        $this->deleteAddresses($customerId, $shipToIds);

        $existingAddresses = $this->getCustomerAddresses($customerId);
        $countryCode = $soldTo->getCountryCode();
        $regionData = $this->getFirstRegionCodeByCountryCode($countryCode);
        // $regionData = $this->getCountryInfo($countryCode);
        foreach ($shipTo as $shipToAddress) {
            $addressExists = $this->addressExists($existingAddresses, $shipToAddress->getId());

            if (!$addressExists) {
                // Crear Address object
                $address = $this->addressInterfaceFactory->create();
                $address->setFirstname($shipToAddress->getName())
                    ->setLastname($soldTo->getCustomerName())
                    ->setCountryId($soldTo->getCountryCode())
                    ->setCity('notDefined')
                    ->setPostcode('111111')
                    ->setCustomerId($customerId)
                    ->setStreet([$shipToAddress->getAddress(), $shipToAddress->getAddressNumber()])
                    ->setTelephone('111111')
                    ->setVatId($shipToAddress->getId())
                    ->setIsDefaultShipping('true');
                $region = $this->regionInterfaceFactory->create();
                $region->setRegionCode($regionData['code']);
                $address->setRegion($region);
                $address->setRegionId($regionData['id']);

                $this->addressRepository->save($address);
            }
        }
        return true;
    }

    /**
     * Get Customer Addresses
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    private function getCustomerAddresses($customerId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $customerId, 'eq')
            ->create();

        $addressCollection = $this->addressRepository->getList($searchCriteria);
        return $addressCollection->getItems();
    }

    /**
     * Check if Address Exists
     *
     * @param array $existingAddresses
     * @param int $shipToId
     * @return bool
     */
    private function addressExists($existingAddresses, $shipToId)
    {
        foreach ($existingAddresses as $address) {
            if ($address->getVatId() == $shipToId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete Addresses
     *
     * @param int $customerId
     * @param array $vatIds
     * @return void
     */
    public function deleteAddresses($customerId, $vatIds)
    {

        $filter1 = $this->filterBuilder->create();
        $filter1->setField('vat_id')
            ->setConditionType('null');
        $filter2 = $this->filterBuilder->create();
        $filter2->setField('parent_id')
            ->setConditionType('eq')
            ->setValue($customerId);
        $filterGroup = $this->filterGroup->setData('filters', [$filter1, $filter2]);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setFilterGroups([$filterGroup]);

        $addressCollection = $this->addressRepository->getList($searchCriteria);
        $addresses = $addressCollection->getItems();

        foreach ($addresses as $address) {
            if (!in_array($address->getVatId(), $vatIds)) {
                $this->addressRepository->delete($address);
            }
        }
    }

    /**
     * Get the first region code associated with the given country code
     *
     * @param string $countryCode
     * @return array|null
     */
    private function getFirstRegionCodeByCountryCode($countryCode)
    {
        $countryInfo = $this->countryInformationAcquirerInterface->getCountryInfo($countryCode);
        $regions = $countryInfo->getAvailableRegions();

        $region = reset($regions);
        if (!empty($region)) {
            $array = [
                'id' => $region->getId(),
                'code' => $region->getCode(),
                'name' => $region->getName()
            ];
            return $array;

        }
        return null;
    }

    /**
     * Get the function code for the current store
     *
     * @return string|null
     */
    public function getFunctionCodeByStoreId()
    {
        $currentStoreId = $this->storeManager->getStore()->getId();

        $configStoreCollection = $this->configStoreCollectionFactory->create();
        $configStoreCollection->addFieldToFilter('southbay_store_code', $currentStoreId);
        $configStoreCollection->addFieldToSelect('southbay_function_code');
        $configStoreCollection->setPageSize(1);

        $configStoreItem = $configStoreCollection->getFirstItem();

        return $configStoreItem->getFunctionCode();
    }

    public function buildRedirect($url)
    {
        $code = $this->getFunctionCodeByStoreId();
        if ($code == 'rtv') {
            $url = $url . 'customer/account/';
        }
        return $url;
    }

    public function getCountryInfo($code)
    {
        $array = [
            'id' => 525,
            'code' => 'AR-B',
            'name' => 'Buenos Aires'
        ];

        if ($code == 'UY') {
            $array = [
                'id' => 1078,
                'code' => 'UY-AR',
                'name' => 'Artigas'
            ];
        }

        return $array;

    }
}
