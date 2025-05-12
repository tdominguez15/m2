<?php

namespace Southbay\Product\Model;

use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory;

class StoreOptions implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,

    ) {
        $this->collectionFactory = $collectionFactory;

    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create();
        $options = [];
        foreach ($collection as $item) {
            if($item->getFunctionCode() !== ConfigStoreRepositoryInterface::SOUTHBAY_AT_ONCE
            && $item->getFunctionCode() !== ConfigStoreRepositoryInterface::SOUTHBAY_FUTURES)
            {
                continue;
            }
            $label = ($item->getFunctionCode() === ConfigStoreRepositoryInterface::SOUTHBAY_AT_ONCE) ? 'At Once' : 'Futuros';
            $options[] = [
                'value' => $item->getStoreCode(),
                'label' => $label . ' ' . $item->getCountryCode()
            ];
        }
        return $options;
    }
}
