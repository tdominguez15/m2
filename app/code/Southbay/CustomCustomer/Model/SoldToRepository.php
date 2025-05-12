<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\SoldToInterface;
use Southbay\CustomCustomer\Api\SoldToRepositoryInterface;
use Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory;
use Southbay\CustomCustomer\Model\SoldToFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class SoldToRepository implements SoldToRepositoryInterface
{
    protected $soldToFactory;
    protected $soldToCollectionFactory;

    public function __construct(
        SoldToFactory     $soldToFactory,
        CollectionFactory $soldToCollectionFactory
    )
    {
        $this->soldToFactory = $soldToFactory;
        $this->soldToCollectionFactory = $soldToCollectionFactory;
    }

    /**
     * @param $id
     * @return SoldToInterface
     */
    public function getById($id)
    {
        $soldTo = $this->soldToFactory->create();
        $soldTo->load($id);
        return $soldTo;
    }

    /**
     * @param $code
     * @return SoldToInterface
     */
    public function getByCustomerCode($code)
    {
        $collection = $this->soldToCollectionFactory->create();
        $collection->addFieldToFilter(SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_CODE,$code);

        if($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    public function createOrUpdate($data)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->soldToCollectionFactory->create();
        $collection->addFilter(SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_CODE, $data['southbay_sold_to_customer_code']);
        $collection->load();

        /**
         * @var SoldToInterface $model
         */
        $model = $collection->getFirstItem();
        if (!$model->getId()) {
            $model = $this->soldToFactory->create();
        }

        foreach ($data as $key => $value) {
            $model->setData($key, $value);
        }

        try {
            $this->save($model);
        } catch (CouldNotSaveException $e) {

            throw $e;
        }
    }

    public function save(SoldToInterface $soldTo)
    {
        $soldTo->save();
        return $soldTo;
    }

    public function delete(SoldToInterface $soldTo)
    {
        $soldTo->delete();
    }
}
