<?php

namespace Southbay\Product\Model;

use Southbay\Product\Model\ResourceModel\SeasonType\CollectionFactory;

class SeasonTypeDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $postCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $items = $this->collection->getItems();
        $data = [];

        foreach ($items as $item) {
            $data[$item->getId()] = $item->getData();
        }
        return $data;
    }
}
