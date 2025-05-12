<?php

namespace Southbay\ReturnProduct\Model;

class ConfirmedReturnProductDataProvider extends ReturnProductDataProvider
{
    protected function createCollection()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = parent::createCollection();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS,
            [
                'in' => [\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONFIRMED, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_DOCUMENTS_SENT,\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CLOSED]
            ]
        );

        return $collection;
    }
}
