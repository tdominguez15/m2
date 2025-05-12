<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayInvoiceRepository
{
    private $collectionFactory;
    private $resource;
    private $log;
    private $cache;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice\CollectionFactory $collectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice                   $resource,
                                \Magento\Framework\Model\Context                                              $context,
                                \Psr\Log\LoggerInterface                                                      $log)
    {
        $this->log = $log;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->cache = $context->getCacheManager();
    }

    /**
     * @param $value
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayInvoice|null
     */
    public function findById($id)
    {
        $identifier = \Southbay\ReturnProduct\Api\Data\SouthbayInvoice::CACHE_TAG . '_' . $id;

        $item = $this->cache->load($identifier);

        if ($item) {
            return unserialize($item);
        } else {
            $collection = $this->findByAttributeNameInvoice(\Southbay\ReturnProduct\Api\Data\SouthbayInvoice::ENTITY_ID, $id);
            if ($collection->count() == 0) {
                return null;
            }
            $item = $collection->getFirstItem();

            $this->cache->save(serialize($item), $identifier, [\Southbay\ReturnProduct\Api\Data\SouthbayInvoice::CACHE_TAG]);

            return $item;
        }
    }

    public function findByInternalInvoiceNumber($value)
    {
        $collection = $this->findByAttributeNameInvoice(\Southbay\ReturnProduct\Api\Data\SouthbayInvoice::ENTITY_INTERNAL_INVOICE_NUMBER, $value);
        if ($collection->count() == 0) {
            return null;
        }
        return $collection->getFirstItem();
    }

    /**
     * @param $id
     * @param $value
     * @return AbstractCollection
     */
    private function findByAttributeNameInvoice($name, $value)
    {
        $collection = $this->collectionFactory->create();
        return $collection->addFieldToFilter($name, ['eq' => $value]);
    }
}
