<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class SouthbayReturnControlQaItemRepository
{
    private $log;
    private $repository;
    private $factory;
    private $collectionFactory;
    private $return_product_repository;
    private $resourceConnection;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQaItem                             $repository,
                                \Southbay\ReturnProduct\Model\SouthbayReturnControlQaItemFactory                                    $factory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaItemCollectionFactory $collectionFactory,
                                SouthbayReturnProductRepository                                                                     $return_product_repository,
                                ResourceConnection                                                                                  $resourceConnection,
                                \Psr\Log\LoggerInterface                                                                            $log)
    {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->return_product_repository = $return_product_repository;
        $this->resourceConnection = $resourceConnection;
        $this->log = $log;
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem $model
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save($model)
    {
        return $this->repository->save($model);
    }
}
