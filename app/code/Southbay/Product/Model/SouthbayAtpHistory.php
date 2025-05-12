<?php

namespace Southbay\Product\Model;

use Southbay\Product\Api\Data\SouthbayAtpHistory as ModelInterface;

class SouthbayAtpHistory extends \Magento\Framework\Model\AbstractModel implements ModelInterface
{
    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbayAtpHistory');
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt($date)
    {
        $this->setData(self::ENTITY_CREATED_AT, $date);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt($date)
    {
        $this->setData(self::ENTITY_UPDATED_AT, $date);
    }

    public function getJsonData()
    {
        return $this->getData(self::ENTITY_JSON_DATA);
    }

    public function setJsonData($data)
    {
        $this->setData(self::ENTITY_JSON_DATA, $data);
    }

    public function getCountryCode()
    {
        return $this->getData(self::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($code)
    {
        $this->setData(self::ENTITY_COUNTRY_CODE, $code);
    }

    public function getSapCountryCode()
    {
        return $this->getData(self::ENTITY_SAP_COUNTRY_CODE);
    }

    public function setSapCountryCode($code)
    {
        $this->setData(self::ENTITY_SAP_COUNTRY_CODE, $code);
    }
}
