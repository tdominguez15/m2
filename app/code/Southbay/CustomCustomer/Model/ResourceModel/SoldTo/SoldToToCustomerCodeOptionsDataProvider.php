<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\SoldTo;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\CustomCustomer\Api\Data\SoldToInterface;

/**
 * Class SoldToToCustomerCodeOptionsDataProvider
 * @package Southbay\CustomCustomer\Model\ResourceModel\SoldTo
 */
class SoldToToCustomerCodeOptionsDataProvider implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $_options;

    /**
     * SoldToToCustomerCodeOptionsDataProvider constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            /**
             * @var AbstractCollection $collection
             */
            $collection = $this->collectionFactory->create();
            $collection->setOrder(SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_CODE, 'ASC');

            $items = $collection->getItems();
            $options = [];

            /**
             * @var SoldToInterface $item
             */
            foreach ($items as $item) {
                $options[] = [
                    'value' => $item->getCustomerCode(),
                    'label' => $item->getCustomerCode() . $item->getCountryCode() . '-' . trim($item->getCustomerName())
                ];
            }

            $this->_options = $options;
        }

        return $this->_options;
    }
}
