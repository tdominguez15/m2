<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

class SouthbayExchangeReturnRepository
{
    private $log;
    private $collectionFactory;
    private $repository;
    private $factory;

    public function __construct(
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayExchangeReturnCollectionFactory $collectionFactory,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayExchangeReturn                             $repository,
        \Southbay\ReturnProduct\Model\SouthbayExchangeReturnFactory                                    $factory,
        \Psr\Log\LoggerInterface                                                                       $log
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->log = $log;
    }

    public function getLastExchange($country)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn::ENTITY_COUNTRY_CODE, ['eq' => $country]);
        $collection->addOrder(\Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn::ENTITY_CREATED_AT, 'DESC');
        $collection->setPageSize(1);
        $collection->setCurPage(1);
        $collection->load();

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        } else {
            return null;
        }
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn|null
     */
    public function findById($id)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        return $collection->getItemById($id);
    }

    /**
     * @param $data
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn
     */
    public function createNewExchange($data)
    {
        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn $model
         */
        $model = $this->factory->create();
        $model->setCountryCode($data['country']);
        $model->setUserCode($data['user_code']);
        $model->setUserName($data['user_name']);
        $model->setExchange($data['exchange']);

        return $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn $modal
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn
     */
    public function save($modal)
    {
        $modal->setExchange(round($modal->getExchange(), 3));
        $this->repository->save($modal);
        return $modal;
    }
}
