<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\ConfigStore;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;

class SouthbayOptionsOnlyAtOnceDataProvider implements OptionSourceInterface
{
    private $collectionFactory;
    private $_options;
    private $storeManager;

    public function __construct(\Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory $collectionFactory,
                                StoreManagerInterface                                                      $storeManager)
    {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(\Southbay\CustomCustomer\Api\Data\ConfigStoreInterface::SOUTHBAY_FUNCTION_CODE, ConfigStoreInterface::FUNCTION_CODE_AT_ONCE);

            $items = $collection->getItems();

            $options = [
                [
                    'value' => '',
                    'label' => __('Seleccione una tienda')
                ]
            ];

            /**
             * @var \Southbay\CustomCustomer\Api\Data\ConfigStoreInterface $item
             */
            foreach ($items as $item) {
                $store = $this->storeManager->getStore($item->getSouthbayStoreCode());

                $options[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName() . '(' . $store->getCode() . ')'
                ];
            }

            $this->_options = $options;
        }

        return $this->_options;
    }
}
