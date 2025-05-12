<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalCollection;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalCollectionFactory;

class SouthbayReturnFinancialApprovalRepository
{
    private $collectionFactory;

    public function __construct(SouthbayReturnFinancialApprovalCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval|null
     */
    public function findByReturnProductId($id)
    {
        /**
         * @var SouthbayReturnFinancialApprovalCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_RETURN_ID, $id);

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }
}
