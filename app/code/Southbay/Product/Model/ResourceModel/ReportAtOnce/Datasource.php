<?php

namespace Southbay\Product\Model\ResourceModel\ReportAtOnce;

class Datasource extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    private $log;
    private $context;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $log,
        array $meta = [],
        array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->log = $log;
        $this->context = $context;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return null;
    }

    public function getCollection()
    {
        return null;
    }


    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }
}
