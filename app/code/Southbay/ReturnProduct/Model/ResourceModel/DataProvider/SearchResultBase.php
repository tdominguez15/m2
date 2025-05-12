<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class SearchResultBase extends SearchResult
{
    protected $context;

    public function __construct(EntityFactory                       $entityFactory,
                                \Magento\Backend\App\Action\Context $context,
                                Logger                              $logger,
                                FetchStrategy                       $fetchStrategy,
                                EventManager                        $eventManager,
                                                                    $mainTable,
                                                                    $resourceModel)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->context = $context;
        \Southbay\ReturnProduct\Model\ResourceModel\DataProvider\DataProviderBase::setupJoin($this);
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->removeAllFieldsFromSelect();
        $this->getSelect()
            ->columns([
                "main_table.*",
                'return_product.southbay_return_type AS southbay_return_type',
                'return_product.southbay_return_customer_code AS southbay_return_customer_code'
            ]);

        parent::load($printQuery, $logQuery);
    }

    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field != 'southbay_return_type' &&
            $field != 'southbay_return_customer_code') {
            if (!str_starts_with('main_table.', $field)) {
                $field = 'main_table.' . $field;
            }
        }

        return parent::setOrder($field, $direction);
    }
}
