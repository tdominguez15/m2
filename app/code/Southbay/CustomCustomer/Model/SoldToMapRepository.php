<?php

namespace Southbay\CustomCustomer\Model;

class SoldToMapRepository
{
    private $collectionFactory;
    private $repository;

    public function __construct(\Southbay\CustomCustomer\Model\ResourceModel\SoldToMap\CollectionFactory $collectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\SoldToMap                   $repository)
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
    }

    public function create($data)
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\SoldToMap\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFilter('sold_to_code', $data['sold_to_code']);
        $collection->addFilter('sold_to_old_code', $data['sold_to_old_code']);

        /**
         * @var \Southbay\CustomCustomer\Model\SoldToMap $model
         */
        $model = $collection->getFirstItem();

        if ($collection->getSize() == 0) {
            $model->setSoldToCode($data['sold_to_code']);
            $model->setSoldToOldCode($data['sold_to_old_code']);
            $this->repository->save($model);

            return true;
        } else {
            return false;
        }
    }

    public function deleteById($ids)
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\SoldToMap\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $ids]);
        $collection->load();

        foreach ($collection->getItems() as $item) {
            $this->repository->delete($item);
        }
    }
}
