<?php

namespace Southbay\Product\Model\ResourceModel;

use Southbay\Product\Api\Data\SouthbayImportProductsDetail as EntityInterface;

class SouthbayImportProductsDetailRepository
{
    protected $collectionFactory;
    protected $repository;
    protected $modelFactory;

    public function __construct(
        \Southbay\Product\Model\ResourceModel\Collection\SouthbayImportProductsDetailCollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayImportProductsDetail                             $repository,
        \Southbay\Product\Model\SouthbayImportProductsDetailFactory                                    $modelFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @return \Southbay\Product\Model\SouthbayImportProductsDetail|null
     */
    public function findById($id)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter(EntityInterface::ENTITY_ID, $id);
        $collection->load();

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return \Southbay\Product\Model\SouthbayImportProductsDetail
     */
    public function getNewModel()
    {
        return $this->modelFactory->create();
    }

    /**
     * @return \Southbay\Product\Model\SouthbayImportProductsDetail
     */
    public function save(\Southbay\Product\Model\SouthbayImportProductsDetail $model)
    {
        $this->repository->save($model);
        return $model;
    }

    public function delete(\Southbay\Product\Model\SouthbayImportProductsDetail $model)
    {
        $this->repository->delete($model);
    }
}
