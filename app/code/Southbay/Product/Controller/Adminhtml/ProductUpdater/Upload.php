<?php

namespace Southbay\Product\Controller\Adminhtml\ProductUpdater;

use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;

class Upload extends \Southbay\Product\Controller\Adminhtml\Product\Upload
{
    protected function getType()
    {
        return SouthbayProductImportHistoryInterface::TYPE_UPDATE;
    }

    protected function getSuccessMessage()
    {
        return __('Archivo de cambio subido exitosamente');
    }

    protected function loadData(\Southbay\Product\Model\SouthbayProductImportHistory $field, $params)
    {
        $field->setStoreId($params['fields']['store_id']);
        $field->setAttributeCode($params['fields']['attribute_code']);
    }
}
