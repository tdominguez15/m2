<?php

namespace Southbay\Product\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Southbay\Product\Api\Data\SouthbayProductsUpdate as EntityInterface;

class SouthbayProductsUpdateRepository
{
    protected $collectionFactory;
    protected $repository;
    protected $modelFactory;
    private $resourceConnection;

    public function __construct(
        \Southbay\Product\Model\ResourceModel\Collection\SouthbayProductsUpdateCollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductsUpdate                             $repository,
        \Southbay\Product\Model\SouthbayProductsUpdateFactory                                    $modelFactory,
        ResourceConnection                                                                       $resourceConnection
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->modelFactory = $modelFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return \Southbay\Product\Model\SouthbayProductsUpdate|null
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
     * @return \Southbay\Product\Model\SouthbayProductsUpdate
     */
    public function getNewModel()
    {
        return $this->modelFactory->create();
    }

    /**
     * @return \Southbay\Product\Model\SouthbayProductsUpdate
     */
    public function save(\Southbay\Product\Model\SouthbayProductsUpdate $model)
    {
        $this->repository->save($model);
        return $model;
    }

    public function delete(\Southbay\Product\Model\SouthbayProductsUpdate $model)
    {
        $this->repository->delete($model);
    }

    /**
     * @param $import_id
     * @return \Southbay\Product\Model\SouthbayProductsUpdate[]
     */
    public function getAllByImportId($import_id)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter(EntityInterface::ENTITY_SEASON_IMPORT_ID, $import_id);
        $collection->load();

        return $collection->getItems();
    }

    public function deleteByImportId($import_id)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(EntityInterface::TABLE);
        $import_id_field = EntityInterface::ENTITY_SEASON_IMPORT_ID;
        $connection->delete($tableName, ["$import_id_field = ?" => $import_id]);
    }
}
