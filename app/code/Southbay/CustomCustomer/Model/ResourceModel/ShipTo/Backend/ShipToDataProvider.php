<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\ShipTo\Backend;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory;

/**
 * Class ShipToDataProvider
 * @package Southbay\CustomCustomer\Model\ResourceModel\ShipTo\Backend
 */
class ShipToDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * ShipToDataProvider constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param LoggerInterface $log
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        Context           $context,
        CollectionFactory $collectionFactory,
                          $name,
                          $primaryFieldName,
                          $requestFieldName,
        LoggerInterface   $log,
        array             $meta = [],
        array             $data = []
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->context = $context;
        $this->log = $log;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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
            $url = $url_builder->getUrl('southbay_custom_customer/shipto/edit', ['id' => $id]);
            $item->setData('link_label', __('Editar'));
            $item->setData('link', $url);
            $this->loadedData['items'][] = $item->getData();
        }

        $this->loadedData['totalRecords'] = $this->getCollection()->getSize();

        return $this->loadedData;
    }

    /**
     * Get collection
     *
     * @return AbstractCollection
     */
    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->initCollection();
        }
        //TODO quitar esta linea cuando se agregue funcionalidad de bajas logicas a la grilla
        $this->collection->addFieldToFilter('southbay_ship_to_is_active', 1);
        return $this->collection;
    }

    /**
     * Initialize collection
     *
     * @return AbstractCollection
     */
    protected function initCollection()
    {
        return $this->collectionFactory->create();
    }
}
