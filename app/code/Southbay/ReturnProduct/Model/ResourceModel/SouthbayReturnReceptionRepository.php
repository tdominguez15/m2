<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnReceptionRepository
{
    private $log;
    private $repository;
    private $factory;
    private $collectionFactory;
    private $return_product_repository;
    private $resourceConnection;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnReception                             $repository,
                                \Southbay\ReturnProduct\Model\SouthbayReturnReceptionFactory                                    $factory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnReceptionCollectionFactory $collectionFactory,
                                SouthbayReturnProductRepository                                                                 $return_product_repository,
                                ResourceConnection                                                                              $resourceConnection,
                                \Psr\Log\LoggerInterface                                                                        $log)
    {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->return_product_repository = $return_product_repository;
        $this->resourceConnection = $resourceConnection;
        $this->log = $log;
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnReception|null
     */
    public function findById($id)
    {
        $collection = $this->collectionFactory->create();
        return $collection->getItemById($id);
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnReception|null
     */
    public function findByReturnProductId($id)
    {
        /**
         * @var AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnReception::ENTITY_RETURN_ID, $id);

        if ($collection->count() === 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    public function updateTotalPackages($id, $total_packages, $user_id, $user_name)
    {
        $field = $this->findById($id);
        if (is_null($field)) {
            throw new \Exception(__('No existe la recepción #') . $id);
        }

        $field->setUserCode($user_id);
        $field->setUserName($user_name);
        $field->setTotalPackages($total_packages);

        $this->repository->save($field);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $return_product
     * @param $data
     * @return false|\Southbay\ReturnProduct\Api\Data\SouthbayReturnReception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save($return_product, $data)
    {
        $connection = $this->resourceConnection->getConnection();

        try {
            $connection->beginTransaction();

            $collection = $this->findByAttributeName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnReception::ENTITY_RETURN_ID, $data['return_id']);
            $collection->setPageSize(1);
            $collection->setCurPage(1);
            $collection->load();

            if ($collection->count() == 0) {
                /**
                 * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnReception $model
                 */
                $model = $this->factory->create();
                $model->setCountryCode($return_product->getCountryCode());
                $model->setReturnId($data['return_id']);
                $model->setUserCode($data['user_id']);
                $model->setUserName($data['user_name']);
                $model->setTotalPackages($data['total_packages']);

                $result = $this->repository->save($model);

                $this->return_product_repository->markAsReceived($return_product);

                $connection->commit();

                return $result;
            } else {
                $connection->rollBack();
                return false;
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param $id
     * @param $value
     * @return AbstractCollection
     */
    private function findByAttributeName($name, $value)
    {
        $collection = $this->collectionFactory->create();
        return $collection->addFieldToFilter($name, ['eq' => $value]);
    }
}
