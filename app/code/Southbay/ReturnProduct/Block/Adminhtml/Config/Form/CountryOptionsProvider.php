<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Directory\Model\CountryFactory;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\Backend\StoreConfigDataProvider;

class CountryOptionsProvider implements OptionSourceInterface
{
    private $options;
    private $context;
    private $countryFactory;

    private $configStoreCollectionFactory;

    public function __construct(\Magento\Backend\Block\Template\Context                                    $context,
                                \Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory $configStoreCollectionFactory,
                                CountryFactory                                                             $countryFactory)
    {
        $this->context = $context;
        $this->countryFactory = $countryFactory;
        $this->configStoreCollectionFactory = $configStoreCollectionFactory;
    }

    public function toOptionArray()
    {
        if (isset($this->options)) {
            return $this->options;
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->configStoreCollectionFactory->create();
        $country_codes = $collection->getColumnValues('southbay_country_code');

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->countryFactory->create()->getCollection();
        $items = $collection->getItems();
        $options = [
            ['value' => '', 'label' => __('Seleccione un país')]
        ];

        foreach ($items as $item) {
            if (!in_array($item->getId(), $country_codes)) {
                continue;
            }
            $options[] = [
                'value' => $item->getId(),
                'label' => __($item->getName())
            ];
        }

        $this->options = $options;

        return $options;
    }
}
