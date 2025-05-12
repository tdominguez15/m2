<?php

namespace Southbay\Issues\Model\ResourceModel;

use Southbay\Issues\Api\Data\SouthbayUiTest as EntityInterface;

class SouthbayUiTestRepository
{
    protected $collectionFactory;
    protected $repository;
    protected $modelFactory;

    public function __construct(
        \Southbay\Issues\Model\ResourceModel\Collection\SouthbayUiTestCollectionFactory $collectionFactory,
        \Southbay\Issues\Model\ResourceModel\SouthbayUiTest                             $repository,
        \Southbay\Issues\Model\SouthbayUiTestFactory                                    $modelFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @return \Southbay\Issues\Model\SouthbayUiTest|null
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
     * @return \Southbay\Issues\Model\SouthbayUiTest
     */
    public function getNewModel()
    {
        return $this->modelFactory->create();
    }

    /**
     * @return \Southbay\Issues\Model\SouthbayUiTest
     */
    public function save(\Southbay\Issues\Model\SouthbayUiTest $model)
    {
        $this->repository->save($model);
        return $model;
    }

    public function delete(\Southbay\Issues\Model\SouthbayUiTest $model)
    {
        $this->repository->delete($model);
    }
}
