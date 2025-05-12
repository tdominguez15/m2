<?php

namespace Southbay\CustomCustomer\Block\Address;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Grid extends \Magento\Framework\View\Element\Template
{
    private $currentCustomer;
    private $addressCollectionFactory;
    private $addressCollection;
    private $countryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        AddressCollectionFactory $addressCollectionFactory,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->countryFactory = $countryFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout(): void
    {
        parent::_prepareLayout();
        $this->preparePager();
    }

    public function getAddAddressUrl(): string
    {
        return $this->getUrl('customer/address/new', ['_secure' => true]);
    }

    public function getDeleteUrl(): string
    {
        return $this->getUrl('customer/address/delete');
    }

    public function getAddressEditUrl($addressId): string
    {
        return $this->getUrl('customer/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    public function getAdditionalAddresses(): array
    {
        $additional = [];
        $addresses = $this->getAddressCollection();
//        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
//         if (!in_array((int)$address->getId(), $primaryAddressIds, true)) {
                $additional[] = $address->getDataModel();
            }
//    }
        return $additional;
    }

    public function getCustomer(): \Magento\Customer\Api\Data\CustomerInterface
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            $customer = $this->currentCustomer->getCustomer();
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    public function getStreetAddress(\Magento\Customer\Api\Data\AddressInterface $address): string
    {
        $street = $address->getStreet();
        if (is_array($street)) {
            $street = implode(', ', $street);
        }
        return $street;
    }

    public function getCountryByCode(string $countryCode): string
    {
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryCode)->getName();
    }

    private function getDefaultBilling(): int
    {
        $customer = $this->getCustomer();
        return (int)$customer->getDefaultBilling();
    }

    private function getDefaultShipping(): int
    {
        $customer = $this->getCustomer();
        return (int)$customer->getDefaultShipping();
    }

    private function preparePager(): void
    {
        $addressCollection = $this->getAddressCollection();
        if (null !== $addressCollection) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.addresses.pager'
            )->setCollection($addressCollection);
            $this->setChild('pager', $pager);
        }
    }

    private function getAddressCollection(): \Magento\Customer\Model\ResourceModel\Address\Collection
    {
        if (null === $this->addressCollection) {
            if (null === $this->getCustomer()) {
                throw new NoSuchEntityException(__('Customer not logged in'));
            }
            $collection = $this->addressCollectionFactory->create();
            $collection->setOrder('entity_id', 'desc');
//            $collection->addFieldToFilter(
//                'entity_id',
//                ['nin' => [$this->getDefaultBilling(), $this->getDefaultShipping()]]
//            );
            $collection->setCustomerFilter([$this->getCustomer()->getId()]);



            $this->addressCollection = $collection;
        }
        return $this->addressCollection;
    }
}

