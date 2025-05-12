<?php

namespace Southbay\Product\Model\ResourceModel\ReportAttribute;

class Datasource extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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
