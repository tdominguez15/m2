<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\CustomerConfig;

use Magento\Framework\Data\OptionSourceInterface;

class MagentoCustomerDataProvider implements OptionSourceInterface
{
    private $customerFactory;
    private $_options;

    public function __construct(\Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        $this->customerFactory = $customerFactory;
    }

    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            /**
             * @var \Magento\Customer\Model\Customer $customer
             */
            $customer = $this->customerFactory->create();
            $collection = $customer->getCollection();

            $collection->load();
            $items = $collection->getItems();
            $options = [];

            /**
             * @var \Magento\Customer\Model\Customer $item
             */
            foreach ($items as $item) {
                $options[] = [
                    'value' => $item->getEmail(),
                    'label' => $item->getEmail() . '-' . $item->getName()
                ];
            }

            return $this->_options = $options;
        }

        return $this->_options;
    }
}
