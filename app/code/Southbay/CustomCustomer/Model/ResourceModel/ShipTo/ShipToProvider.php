<?php
namespace Southbay\CustomCustomer\Model\ResourceModel\ShipTo;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory;

/**
 * Class DataSource
 * @package Southbay\ShipTo\Model\ResourceModel\ShipTo
 */
class ShipToProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * DataSource constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
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
        $id = $this->request->getParam('id');

        if ($id) {
            $item = $this->getCollection()->getItemById($id);
            if ($item) {
                $this->loadedData[$item->getId()] = $item->getData();
            }
        }

        return $this->loadedData;
    }
}
