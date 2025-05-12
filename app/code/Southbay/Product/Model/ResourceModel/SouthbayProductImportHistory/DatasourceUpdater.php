<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory;

use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;

class DatasourceUpdater extends Datasource
{
    protected function addFieldToFilter()
    {
        $this->collection->addFieldToFilter('type', ['eq' => SouthbayProductImportHistoryInterface::TYPE_UPDATE]);
        $this->collection->addFieldToFilter('season_id', ['eq' => 0]);
    }
}
