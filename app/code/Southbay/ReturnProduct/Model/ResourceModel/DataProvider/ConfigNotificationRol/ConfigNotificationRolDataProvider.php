<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\ConfigNotificationRol;

use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayConfigNotificationRtvByRolCollectionFactory as CollectionFactory;
class ConfigNotificationRolDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    private $collection_factory;
    private $context;
    private $log;
    private $loadedData;

    public function __construct(\Magento\Backend\App\Action\Context $context,
                                CollectionFactory                   $collection_factory,
                                                                    $name,
                                                                    $primaryFieldName,
                                                                    $requestFieldName,
                                \Psr\Log\LoggerInterface            $log,
                                array                               $meta = [],
                                array                               $data = [])
    {
        $this->collection_factory = $collection_factory;
        $this->context = $context;
        $this->log = $log;
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
            $this->collection = $this->initCollection();
        }
        return $this->collection;
    }

    protected function initCollection()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collection_factory->create();

        return $collection;
    }

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
                $data = $item->getData();
                $this->loadedData[$item->getId()]['fields'] = $data;
            }

            return $this->loadedData;
        }

        $this->loadedData['items'] = [];
        $items = $this->getCollection()->getItems();
        $url_builder = $this->context->getBackendUrl();

        foreach ($items as $item) {
            $id = $item->getId();
            $url = $url_builder->getUrl('southbay_return_product/confignotificationrol/view', ['id' => $id]);
            $item->setData('link_label', __('Ver'));
            $item->setData('link', $url);
            $this->loadedData['items'][] = $item->getData();
        }

        $this->loadedData['totalRecords'] = $this->getCollection()->getSize();

        return $this->loadedData;
    }
}
