<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig as EntityInterface;

class SouthbayReturnConfig extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EntityInterface::TABLE, EntityInterface::ENTITY_ID);
    }

    public static function getTypeName($type)
    {
        $result = '';

        if ($type == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD) {
            $result = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_NAME_GOOD;
        } else if (\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL) {
            $result = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_NAME_FAIL;
        }

        return $result;
    }
}
