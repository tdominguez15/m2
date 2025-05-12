<?php

namespace Southbay\Issues\Model\ResourceModel\DataProvider;

class UiTestDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    private $collection_factory;
    private $context;
    private $loadedData;

    public function __construct(\Magento\Backend\App\Action\Context                                             $context,
                                \Southbay\Issues\Model\ResourceModel\Collection\SouthbayUiTestCollectionFactory $collection_factory,
                                                                                                                $name,
                                                                                                                $primaryFieldName,
                                                                                                                $requestFieldName,
                                array                                                                           $meta = [],
                                array                                                                           $data = [])
    {
        $this->collection_factory = $collection_factory;
        $this->context = $context;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data);
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->collection_factory->create();
        }
        return $this->collection;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $id = $this->context->getRequest()->getParam('id');

        if ($id) {
            /**
             * @var \Southbay\Issues\Model\SouthbayUiTest $model
             */
            $model = $this->collection->getItemById($id);
            if ($model) {
                $data = $model->getData();
                $this->loadedData[$id]['fields'] = $data;
                $this->loadedData[$id]['fields']['result'] = json_decode($model->getResult());
            }

            return $this->loadedData;
        }

        $this->loadedData['items'] = [];
        $items = $this->getCollection()->getItems();

        foreach ($items as $item) {
            $data = $item->getData();
            unset($data['content']);
            unset($data['result']);

            $this->loadedData['items'][] = $data;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $log = $objectManager->get('Psr\Log\LoggerInterface');
        $log->debug('UiTestDataProvider', ['items' => $this->loadedData['items']]);

        $this->loadedData['totalRecords'] = count($items);

        return $this->loadedData;
    }
}
