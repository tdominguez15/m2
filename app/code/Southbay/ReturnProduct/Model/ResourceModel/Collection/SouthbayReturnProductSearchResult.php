<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\Collection;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProduct;
use Psr\Log\LoggerInterface as Logger;

class SouthbayReturnProductSearchResult extends SearchResult
{
    public function __construct(EntityFactory $entityFactory,
                                Logger        $logger,
                                FetchStrategy $fetchStrategy,
                                EventManager  $eventManager,
                                              $mainTable = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TABLE,
                                              $resourceModel = SouthbayReturnProduct::class)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addExpressionFieldToSelect('southbay_return_aux','main_table.southbay_return_id','southbay_return_aux');

        $tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($tableDescription as $columnInfo) {
            $this->addFilterToMap($columnInfo['COLUMN_NAME'], 'main_table.' . $columnInfo['COLUMN_NAME']);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        return parent::addFieldToFilter($field, $condition);
    }
}
