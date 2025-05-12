<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\CustomerConfigRepositoryInterface;

use Southbay\CustomCustomer\Api\Data\CustomerConfigInterface;
use Southbay\CustomCustomer\Model\CustomerConfigFactory;
use Southbay\CustomCustomer\Model\ResourceModel\CustomerConfig\CollectionFactory;
use Southbay\CustomCustomer\Model\ResourceModel\CustomerConfig as EntityRepository;

class CustomerConfigRepository implements CustomerConfigRepositoryInterface
{
    protected $collectionFactory;
    protected $entityFactory;
    protected $repository;

    public function __construct(
        EntityRepository      $repository,
        CustomerConfigFactory $entityFactory,
        CollectionFactory     $collectionFactory
    )
    {
        $this->repository = $repository;
        $this->entityFactory = $entityFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @param $id
     * @return CustomerConfigInterface|null
     */
    public function getById($id)
    {
        $collection = $this->getCollection();
        return $collection->getItemById($id);
    }

    /**
     * @param mixed $data
     * @return CustomerConfigInterface
     */
    public function save($data)
    {
        $model = $this->findByCustomerEmail($data['email']);

        if (is_null($model)) {
            /**
             * @var CustomerConfigInterface $model
             */
            $model = $this->entityFactory->create();
        }

        $model->setMagentoCustomerEmail($data['email']);
        $model->setSoldToIds($data['sold_to_ids']);
        $model->setFunctionsCodes($data['functions_codes']);
        $model->setCountriesCodes($data['countries_codes']);

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Psr\Log\LoggerInterface $log
         */
        $log = $objectManager->get('Psr\Log\LoggerInterface');
        $log->debug('model', ['m' => $model->getData()]);

        $this->repository->save($model);

        return $model;
    }

    public function delete($field)
    {
        $this->repository->delete($field);
    }

    /**
     * @param $email
     * @return CustomerConfigInterface|null
     */
    public function findByCustomerEmail($email)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter(CustomerConfigInterface::ENTITY_MAGENTO_CUSTOMER_EMAIL, $email);
        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        } else {
            return null;
        }
    }
}
