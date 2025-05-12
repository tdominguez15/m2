<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayInvoice as SouthbayIvoiceInterfase;

class SouthbayInvoice extends \Magento\Framework\Model\AbstractModel implements SouthbayIvoiceInterfase
{
    protected $_cacheTag = false; //SouthbayIvoiceInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayIvoiceInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice');
    }

    public function getIdentities(): array
    {
        return [SouthbayIvoiceInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getCountryCode()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_COUNTRY_CODE, $value);
    }

    public function getOldInvoice()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_OLD_INVOICE);
    }

    public function setOldInvoice($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_OLD_INVOICE, $value);
    }

    public function getCustomerCode()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_CODE);
    }

    public function setCustomerCode($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_CODE, $value);
    }

    public function getCustomerName()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_NAME);
    }

    public function setCustomerName($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_NAME, $value);
    }

    public function getCustomerShipToCode()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_CODE);
    }

    public function setCustomerShipToCode($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_CODE, $value);
    }

    public function getDivCode()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_DIV_CODE);
    }

    public function setDivCode($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_DIV_CODE, $value);
    }

    public function getInvoiceDate()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_INVOICE_DATE);
    }

    public function setInvoiceDate($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_INVOICE_DATE, $value);
    }

    public function getIntInvoiceNum()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_INTERNAL_INVOICE_NUMBER);
    }

    public function setIntInvoiceNum($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_INTERNAL_INVOICE_NUMBER, $value);
    }

    public function getInvoiceRef()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_INVOICE_REF);
    }

    public function setInvoiceRef($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_INVOICE_REF, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_CREATED_AT);
    }

    public function setCreatedAt($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function setCustomerShipToName($value)
    {
        $this->setData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_NAME, $value);
    }

    public function getCustomerShipToName()
    {
        return $this->getData(SouthbayIvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_NAME);
    }
}
