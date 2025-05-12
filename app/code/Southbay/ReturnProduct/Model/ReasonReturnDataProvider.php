<?php

namespace Southbay\ReturnProduct\Model;

class ReasonReturnDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    private $collection_factory;
    protected $loadedData;
    private $log;
    private $context;

    public function __construct($name,
        $primaryFieldName,
        $requestFieldName,
                                \Magento\Backend\App\Action\Context $context,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReasonReturnCollectionFactory $collection_factory,
                                \Psr\Log\LoggerInterface $log,
                                array $meta = [],
                                array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection_factory = $collection_factory;
        $this->log = $log;
        $this->context = $context;
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->collection_factory->create();
        }
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
                $this->loadedData[$item->getId()]['southbay_reason_return'] = $item->getData();
            }
        }

        return $this->loadedData;
    }
}
