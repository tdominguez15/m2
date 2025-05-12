<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\SoldTo;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AvailableCountriesDataProvider implements OptionSourceInterface
{
    private $_options;
    private $countryCollectionFactory;
    private $scopeConfig;

    public function __construct(
        CountryCollectionFactory $countryCollectionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            $allowedCountryCodes = $this->getAllowedCountryCodes();
            $countries = $this->getAvailableCountries($allowedCountryCodes);

            $this->_options = [];
            foreach ($countries as $country) {
                $this->_options[] = [
                    'value' => $country->getCountryId(),
                    'label' => $country->getName()
                ];
            }
        }

        return $this->_options;
    }

    private function getAllowedCountryCodes()
    {
        $allowedCountryCodes = $this->scopeConfig->getValue(
            'general/country/allow',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return explode(',', $allowedCountryCodes);
    }

    private function getAvailableCountries($allowedCountryCodes)
    {
        $countryCollection = $this->countryCollectionFactory->create();
        $countryCollection->addFieldToFilter('country_id', ['in' => $allowedCountryCodes]);

        return $countryCollection->getItems();
    }
}
