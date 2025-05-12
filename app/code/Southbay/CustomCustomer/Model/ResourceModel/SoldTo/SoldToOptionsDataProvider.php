<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\SoldTo;

use Magento\Framework\Data\OptionSourceInterface;

class SoldToOptionsDataProvider implements OptionSourceInterface
{
    private $collectionFactory;
    private $_options;

    public function __construct(\Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->collectionFactory->create();
            $collection->setOrder(\Southbay\CustomCustomer\Api\Data\SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_CODE, 'ASC');

            $items = $collection->getItems();
            $options = [];

            /**
             * @var \Southbay\CustomCustomer\Api\Data\SoldToInterface $item
             */
            foreach ($items as $item) {
                $options[] = [
                    'value' => $item->getId(),
                    'label' => $item->getCustomerCode() . '-' . $item->getCountryCode() . '-' . trim($item->getCustomerName())
                ];
            }

            $this->_options = $options;
        }

        return $this->_options;
    }
}
