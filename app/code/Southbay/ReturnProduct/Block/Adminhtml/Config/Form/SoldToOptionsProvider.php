<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;

class SoldToOptionsProvider implements OptionSourceInterface
{
    private $collectionFactory;
    private $helper;

    public function __construct(\Southbay\ReturnProduct\Helper\Data                                   $helper,
                                \Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory $collectionFactory)
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
        $collection->setOrder(\Southbay\CustomCustomer\Api\Data\SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_CODE, 'ASC');
        $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\SoldToInterface::SOUTHBAY_SOLD_TO_COUNTRY_CODE, ['in' => $countries]);
        $collection->load();

        $items = $collection->getItems();
        $options = [
            ['value' => '', 'label' => __('Seleccione un solicitante')]
        ];

        /**
         * @var \Southbay\CustomCustomer\Api\Data\SoldToInterface $item
         */
        foreach ($items as $item) {
            $options[] = [
                'value' => $item->getCustomerCode(),
                'label' => $item->getCustomerCode() . '-' . $item->getCustomerName() . ' (' . $item->getCountryCode() . ')'
            ];
        }

        return $options;
    }
}
