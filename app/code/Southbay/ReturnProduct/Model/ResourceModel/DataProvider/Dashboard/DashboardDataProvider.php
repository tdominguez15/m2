<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\Dashboard;


use Psr\Log\LoggerInterface;

class DashboardDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    private $returnProductRepository;

    private $log;

    private $sessionManager;

    private $_filters = [];

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $returnProductRepository,
                                                                                                            $name,
                                                                                                            $primaryFieldName,
                                                                                                            $requestFieldName,
                                \Magento\Framework\Session\SessionManager                                   $sessionManager,
                                LoggerInterface                                                             $log,
                                array                                                                       $meta = [],
                                array                                                                       $data = [])
    {
        $this->returnProductRepository = $returnProductRepository;
        $this->log = $log;
        $this->sessionManager = $sessionManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->returnProductRepository->getDashboardCollection();
        }
        return $this->collection;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->_filters[] = $filter;
        parent::addFilter($filter);
    }

    public function getData()
    {
        $this->sessionManager->setDashboardFilters($this->_filters);
        return parent::getData();
    }
}
