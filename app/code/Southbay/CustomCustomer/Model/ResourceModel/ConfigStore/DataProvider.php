<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\ConfigStore;

use Magento\Framework\App\Request\DataPersistorInterface;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory;

/**
 * DataProvider retrieves data for admin form fields.
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $configCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $configCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $configCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Retrieve data.
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $config) {
            $this->loadedData[$config->getId()] = $config->getData();
        }
        $data = $this->dataPersistor->get('southbay_config_store');
        if (!empty($data)) {
            $config = $this->collection->getNewEmptyItem();
            $config->setData($data);
            $this->loadedData[$config->getId()] = $config->getData();
            $this->dataPersistor->clear('southbay_config_store');
        }
        return $this->loadedData;
    }
}
