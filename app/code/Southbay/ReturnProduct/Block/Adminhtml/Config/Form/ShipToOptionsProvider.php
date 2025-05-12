<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;

class ShipToOptionsProvider implements OptionSourceInterface
{
    private $collectionFactory;
    private $helper;

    public function __construct(\Southbay\ReturnProduct\Helper\Data                                   $helper,
                                \Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
    }

    public function toOptionArray()
    {
        $countries = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK);

        if (empty($countries)) {
            return [];
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->setOrder(\Southbay\CustomCustomer\Api\Data\ShipToInterface::SOUTHBAY_SHIP_TO_CODE, 'ASC');
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ShipToInterface::SOUTHBAY_SHIP_TO_COUNTRY_CODE, ['in' => $countries]);
        $collection->load();

        $items = $collection->getItems();
        $options = [
            ['value' => '', 'label' => __('Seleccione una puerta')]
        ];

        /**
         * @var \Southbay\CustomCustomer\Api\Data\ShipToInterface $item
         */
        foreach ($items as $item) {
            $options[] = [
                'value' => $item->getCustomerCode(),
                'label' => $item->getCustomerCode() . ' (' . $item->getCountryCode() . ')'
            ];
        }

        return $options;
    }
}
