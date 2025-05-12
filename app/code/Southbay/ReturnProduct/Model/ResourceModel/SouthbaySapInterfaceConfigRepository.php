<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

class SouthbaySapInterfaceConfigRepository
{
    private $collectionFactory;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapInterfaceConfigCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $type
     * @return \Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig|null
     */
    public function getConfigByType($type)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::ENTITY_TYPE, ['eq' => $type]);

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @param $url
     * @return \Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig|null
     */
    public function getConfigByUrl($url)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterfaceConfig::ENTITY_URL, ['eq' => $url]);

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }
}
