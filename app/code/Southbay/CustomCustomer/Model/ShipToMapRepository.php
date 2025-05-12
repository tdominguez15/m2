<?php

namespace Southbay\CustomCustomer\Model;

class ShipToMapRepository
{
    private $collectionFactory;
    private $repository;

    public function __construct(\Southbay\CustomCustomer\Model\ResourceModel\ShipToMap\CollectionFactory $collectionFactory,
                                \Southbay\CustomCustomer\Model\ResourceModel\ShipToMap                   $repository)
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
    }

    public function create($data)
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\ShipToMap\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFilter('sold_to_code', $data['sold_to_code']);
        $collection->addFilter('sold_to_old_code', $data['sold_to_old_code']);
        $collection->addFilter('ship_to_code', $data['ship_to_code']);
        $collection->addFilter('ship_to_old_code', $data['ship_to_old_code']);

        /**
         * @var \Southbay\CustomCustomer\Model\ShipToMap $model
         */
        $model = $collection->getFirstItem();

        if ($collection->getSize() == 0) {
            $model->setSoldToCode($data['sold_to_code']);
            $model->setSoldToOldCode($data['sold_to_old_code']);
            $model->setShipToCode($data['ship_to_code']);
            $model->setShipToOldCode($data['ship_to_old_code']);
            $this->repository->save($model);

            return true;
        } else {
            return false;
        }
    }

    public function deleteById($ids)
    {
        /**
         * @var \Southbay\CustomCustomer\Model\ResourceModel\ShipToMap\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $ids]);
        $collection->load();

        foreach ($collection->getItems() as $item) {
            $this->repository->delete($item);
        }
    }
}
