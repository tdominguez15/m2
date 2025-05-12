<?php

namespace Southbay\Product\Model\ResourceModel\ProductExclusion;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Backend\App\Action\Context;
use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\Product\Model\ResourceModel\ProductExclusion\CollectionFactory;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;

class Datasource extends AbstractDataProvider
{
    protected $collection;
    protected $loadedData;
    protected $context;
    protected $configStoreRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Context $context,
        CollectionFactory $collectionFactory,
        ConfigStoreRepository $configStoreRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->context = $context;
        $this->collection = $collectionFactory->create();
        $this->configStoreRepository = $configStoreRepository;
    }

    /**
     * Get collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get data
     *
     * @return array
     */

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $id = $this->context->getRequest()->getParam('id');

        if ($id) {
            $item = $this->collection->getItemById($id);
            if ($item) {
                $storeId = $item->getStoreId();
                $configStore = $this->configStoreRepository->findByStoreId($storeId);
                if ($configStore) {
                    $storeData = $configStore->getData();
                    $item->setData('store', $storeData['southbay_function_code'] . ' ' . $storeData['southbay_country_code']);
                }
                $this->loadedData[$item->getId()]['fields'] = $item->getData();
            }
        } else {
            $items = $this->collection->getItems();
            if (!empty($items)) {
                foreach ($items as $item) {
                    $storeId = $item->getStore();
                    $configStore = $this->configStoreRepository->findByStoreId($storeId);
                    if ($configStore) {
                        $storeData = $configStore->getData();
                        if($storeData['southbay_function_code'] === ConfigStoreRepositoryInterface::SOUTHBAY_AT_ONCE){
                            $item->setData('store', 'At Once ' . $storeData['southbay_country_code']);
                        }
                         else {
                             $item->setData('store', 'Futuros ' . $storeData['southbay_country_code']);
                         }
                    } else {
                        $item->setData('store', 'Unknown Store');
                    }
                    $data = $item->toArray();
                    $this->loadedData['items'][] = $data;
                }
                $this->loadedData['totalRecords'] = $this->collection->getSize();
            } else {

                $this->loadedData['items'] = [];
                $this->loadedData['totalRecords'] = 0;
            }
        }

        return $this->loadedData;
    }
}
