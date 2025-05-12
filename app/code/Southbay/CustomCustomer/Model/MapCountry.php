<?php

namespace Southbay\CustomCustomer\Model;

use Magento\Framework\Model\AbstractModel;
use Southbay\CustomCustomer\Api\Data\MapCountryInterface;

/**
 * Class MapCountry
 * @package Southbay\MapCountry\Model
 */
class MapCountry extends AbstractModel
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Southbay\CustomCustomer\Model\ResourceModel\MapCountry::class);
    }

    /**
     * Get map country ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_getData('southbay_map_country_id');
    }


    /**
     * Get country code.
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->_getData('southbay_map_country_code');
    }

    /**
     * Set country code.
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        return $this->setData('southbay_map_country_code', $countryCode);
    }

    /**
     * Set country code.
     *
     * @param string $value
     * @return $this
     */
    public function setSapCountryCodeFrontera(string $value)
    {
        return $this->setData('southbay_map_sap_country_code_frontera', $value);
    }

    /**
     * Get SAP country code.
     *
     * @return string|null
     */
    public function getSapCountryCodeFrontera()
    {
        return $this->_getData('southbay_map_sap_country_code_frontera');
    }

    /**
     * Set SAP country code.
     *
     * @param string $sapCountryCode
     * @return $this
     */
    public function setSapCountryCode($sapCountryCode)
    {
        return $this->setData('southbay_map_sap_country_code', $sapCountryCode);
    }

    public function getSapCountryCode()
    {
        return $this->getData('southbay_map_sap_country_code');
    }

    /**
     * Set SAP country code.
     *
     * @param string $stockId
     * @return $this
     */
    public function setStockId($stockId)
    {
        return $this->setData('southbay_map_stock_id', $stockId);
    }

    public function getStockId()
    {
        return $this->getData('southbay_map_stock_id');
    }

    /**
     * Get creation time.
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_getData('created_at');
    }

    /**
     * Set creation time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData('created_at', $createdAt);
    }

    /**
     * Get update time.
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_getData('updated_at');
    }

    /**
     * Set update time.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData('updated_at', $updatedAt);
    }

    public function setSapChannel($value)
    {
        return $this->setData(MapCountryInterface::SOUTHBAY_MAP_SAP_CHANNEL, $value);
    }

    public function getSapChannel()
    {
        return $this->_getData(MapCountryInterface::SOUTHBAY_MAP_SAP_CHANNEL);
    }

    public function getSapZone()
    {
        return $this->_getData(MapCountryInterface::SOUTHBAY_MAP_SAP_ZONE);
    }

    public function setSapZone($sapZone)
    {
        return $this->setData(MapCountryInterface::SOUTHBAY_MAP_SAP_ZONE, $sapZone);
    }

    public function getSapFutureDoc()
    {
        return $this->_getData(MapCountryInterface::SOUTHBAY_MAP_SAP_FUTURE_DOC);
    }

    public function setSapFutureDoc($sapFutureDoc)
    {
        return $this->setData(MapCountryInterface::SOUTHBAY_MAP_SAP_FUTURE_DOC, $sapFutureDoc);
    }

    public function getSapAtOnceDoc()
    {
        return $this->_getData(MapCountryInterface::SOUTHBAY_MAP_SAP_AT_ONCE_DOC);
    }

    public function setSapAtOnceDoc($sapAtOnceDoc)
    {
        return $this->setData(MapCountryInterface::SOUTHBAY_MAP_SAP_AT_ONCE_DOC, $sapAtOnceDoc);
    }

    public function setSouthbayMapSapSourceCode($value)
    {
        return $this->setData(MapCountryInterface::SOUTHBAY_MAP_SAP_SOURCE_CODE, $value);
    }

    public function getSouthbayMapSapSourceCode()
    {
        return $this->_getData(MapCountryInterface::SOUTHBAY_MAP_SAP_SOURCE_CODE);
    }

    public function setSouthbayMapSapWarehouseCode($value)
    {
        return $this->setData(MapCountryInterface::SOUTHBAY_MAP_SAP_WAREHOSE_CODE, $value);
    }

    public function getSouthbayMapSapWarehouseCode()
    {
        return $this->_getData(MapCountryInterface::SOUTHBAY_MAP_SAP_WAREHOSE_CODE);
    }

    public function setSouthbayMapStockId($value)
    {
        return $this->setData(MapCountryInterface::SOUTHBAY_MAP_STOCK_ID, $value);
    }

    public function getSouthbayMapStockId()
    {
        return $this->_getData(MapCountryInterface::SOUTHBAY_MAP_STOCK_ID);
    }
}
