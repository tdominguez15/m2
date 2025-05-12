<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\ShipTo\Backend;


use Magento\Directory\Model\ResourceModel\Country\Collection;

class RegionDataProvider implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Countries
     *
     * @var Collection
     */
    protected $_countryCollection;

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * @param Collection $countryCollection
     */
    public function __construct(Collection $countryCollection)
    {
        $this->_countryCollection = $countryCollection;
    }

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
        if (!$this->_options) {
            $this->_options = $this->_countryCollection->loadData()->setForegroundCountries(
                $foregroundCountries
            )->toOptionArray(
                false
            );
        }

        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }
}
