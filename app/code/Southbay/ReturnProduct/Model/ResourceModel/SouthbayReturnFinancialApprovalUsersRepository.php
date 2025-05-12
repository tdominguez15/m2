<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

class SouthbayReturnFinancialApprovalUsersRepository
{
    private $collectionFactory;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalUsersCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $user_id
     * @param $return_product_id
     * @return \Southbay\ReturnProduct\Model\SouthbayReturnFinancialApprovalUsers|null
     */
    public function findUserByReturnId($user_id, $return_product_id)
    {
        /**
         * @var \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalUsersCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('southbay_return_id', $return_product_id);
        $collection->addFieldToFilter('user_code', $user_id);

        if ($collection->count() === 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @param $return_product_id
     * @return int
     */
    public function getTotalUserByReturnId($return_product_id)
    {
        $collection = $this->getAllByReturnId($return_product_id);

        return $collection->count();
    }

    /**
     * @param $return_product_id
     * @return array
     */
    public function getPendingUsers($return_product_id)
    {
        $collection = $this->getAllByReturnId($return_product_id);
        $collection->load();
        $items = $collection->getItems();

        $result = [];

        /**
         * @var \Southbay\ReturnProduct\Model\SouthbayReturnFinancialApprovalUsers $item
         */
        foreach ($items as $item) {
            if (is_null($item->getApproved())) {
                $result[] = $item->getUserName();
            } else if (!$item->getApproved()) {
                $result = [];
                break;
            }
        }

        return $result;
    }

    /**
     * @param $return_product_id
     * @return array
     */
    public function getApprovalsUsersResponse($return_product_id)
    {
        $collection = $this->getAllByReturnId($return_product_id);
        $collection->load();
        $items = $collection->getItems();

        $result = [];

        /**
         * @var \Southbay\ReturnProduct\Model\SouthbayReturnFinancialApprovalUsers $item
         */
        foreach ($items as $item) {
            if (!is_null($item->getApproved())) {
                $result[] = [
                    'username' => $item->getUserName(),
                    'ok' => $item->getApproved()
                ];
            }
        }

        return $result;
    }

    private function getAllByReturnId($return_product_id)
    {
        /**
         * @var \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalUsersCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('southbay_return_id', $return_product_id);

        return $collection;
    }
}
