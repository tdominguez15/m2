<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\ConfigStore;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class MagentoStoreDataProvider implements OptionSourceInterface
{
    private $storeManager;
    private $_options;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            $stores = $this->storeManager->getStores();
            $options = [];

            foreach ($stores as $store) {
                $options[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName() . ' (' . $store->getCode() . ')'
                ];
            }

            return $this->_options = $options;
        }

        return $this->_options;
    }
}
