<?php

namespace Southbay\ReturnProduct\Block\Frontend;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;

class MyReturns extends \Magento\Framework\View\Element\Template
{
    protected $repository;
    protected $customerSession;

    public function __construct(Template\Context                                                            $context,
                                array                                                                       $data,
                                CustomerSession                                                             $customerSession,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $repository
    )
    {
        if (empty($data)) {
            $data = [];
        }

        parent::__construct($context, $data);
        $this->repository = $repository;
        $this->customerSession = $customerSession;
    }

    protected function getCollection()
    {
        return ($this->getName() == 'my_returns' ? $this->getAllRecentCollection() : $this->getAllCollection());
    }

    protected function getAllRecentCollection()
    {
        return $this->repository->getAllRecent($this->customerSession->getCustomer()->getEmail());
    }

    protected function getAllCollection()
    {
        return $this->repository->getAll($this->customerSession->getCustomer()->getEmail());
    }

    public function getList()
    {
        $collection = $this->getCollection();

        if(is_null($collection)) {
            return [];
        }

        if (!$collection->isLoaded()) {
            $collection->load();
        }

        return $collection;
    }

    public function activeShowAll()
    {
        if ($this->getName() == 'my_returns') {
            $collection_all = $this->getAllCollection();

            if (is_null($collection_all)) {
                return false;
            }

            $collection_recent = $this->getAllRecentCollection();

            if ($collection_all->getSize() > $collection_recent->getSize()) {
                return true;
            }
        }

        return false;
    }

    public function getTypeName($type)
    {
        $result = '';

        if ($type == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD) {
            $result = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_NAME_GOOD;
        } else if (\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL) {
            $result = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_NAME_FAIL;
        }

        return $result;
    }

    public function getItemId($item)
    {
        if ($item->getType() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD) {
            if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT) {
                return '--';
            }
        }

        return $item->getId();
    }

    public function cancellable($item)
    {
        return $this->repository->cancelableByCustomer($item);
    }

    public function printable($item)
    {
        return $this->repository->printable($item);
    }

    public function getName()
    {
        return 'my_returns';
    }
}
