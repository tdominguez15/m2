<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem as SouthbayInvoiceItemInterfase;

class SouthbayInvoiceItem extends \Magento\Framework\Model\AbstractModel implements SouthbayInvoiceItemInterfase
{
    protected $_cacheTag = false; //SouthbayInvoiceItemInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayInvoiceItemInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItem');
    }

    public function getIdentities(): array
    {
        return [SouthbayInvoiceItemInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getInvoiceId()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_INVOICE_ID);
    }

    public function setInvoiceId($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_INVOICE_ID, $value);
    }

    public function getSku()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_SKU);
    }

    public function setSku($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_SKU, $value);
    }

    public function getSku2()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_SKU2);
    }

    public function setSku2($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_SKU2, $value);
    }

    public function getName()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_NAME);
    }

    public function setName($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_NAME, $value);
    }

    public function getSize()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_SIZE);
    }

    public function setSize($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_SIZE, $value);
    }

    public function getQty()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_QTY);
    }

    public function setQty($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_QTY, $value);
    }

    public function getAmount()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_AMOUNT);
    }

    public function setAmount($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_AMOUNT, $value);
    }

    public function getUnitPrice()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_UNIT_PRICE);
    }

    public function setUnitPrice($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_UNIT_PRICE, $value);
    }

    public function getNetAmount()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_NET_AMOUNT);
    }

    public function setNetAmount($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_NET_AMOUNT, $value);
    }

    public function setCreatedAt($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_UPDATED_AT);
    }

    public function setSkuVariant($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_SKU_VARIANT, $value);
    }

    public function getSkuVariant()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_SKU_VARIANT);
    }

    public function setSkuGeneric($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_SKU_GENERIC, $value);
    }

    public function getSkuGeneric()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_SKU_GENERIC);
    }

    public function setNetUnitPrice($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_NET_UNIT_PRICE, $value);
    }

    public function getNetUnitPrice()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_NET_UNIT_PRICE);
    }

    public function setBu($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_BU, $value);
    }

    public function getBu()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_BU);
    }

    public function getPosition()
    {
        return $this->getData(SouthbayInvoiceItemInterfase::ENTITY_POSITION);
    }

    public function setPosition($value)
    {
        $this->setData(SouthbayInvoiceItemInterfase::ENTITY_POSITION, $value);
    }
}
