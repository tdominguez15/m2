<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

class SouthbayRolConfigRtvRepository
{
    private $log;
    private $collectionFactory;
    private $repository;
    private $factory;

    public function __construct(
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayRolConfigRtvCollectionFactory $collectionFactory,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayRolConfigRtv                             $repository,
        \Southbay\ReturnProduct\Model\SouthbayRolConfigRtvFactory                                    $factory,
        \Psr\Log\LoggerInterface                                                                     $log
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->log = $log;
    }

    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function getAll()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->load();

        return $collection->getItems();
    }

    public function getRolesByReturnTypeRol($type_rol, $country_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE_ROL, $type_rol);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_COUNTRY_CODE, $country_code);
        $collection->load();

        return $collection->getItems();
    }

    public function getApprovalRolesByCountyCode($country_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_COUNTRY_CODE, $country_code);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE_ROL, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);
        $collection->load();

        return $collection->getItems();
    }

    /**
     * @param $data
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv
     */
    public function findOrNew($data)
    {
        if (empty($data)) {
            return $this->factory->create();
        } else {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_COUNTRY_CODE, $data['country']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE, $data['type']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE_ROL, $data['type_rol']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_ROL_CODE, $data['rol_code']);

            return $collection->getFirstItem();
        }
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $model
     */
    public function save($model)
    {
        $this->repository->save($model);

        return $model;
    }
}
