<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem as SouthbayReturnProductItemInterfase;

class SouthbayReturnProductItem extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnProductItemInterfase
{
    protected $_cacheTag = SouthbayReturnProductItemInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayReturnProductItemInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItem');
    }

    public function getIdentities(): array
    {
        return [SouthbayReturnProductItemInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }


    public function setCreatedAt($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_UPDATED_AT);
    }


    public function setReturnId($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_RETURN_ID, $value);
    }

    public function getReturnId()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_RETURN_ID);
    }

    public function setInvoiceId($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ID, $value);
    }

    public function getInvoiceid()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ID);
    }

    public function setInvoiceItemId($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ITEM_ID, $value);
    }

    public function getInvoiceItemId()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ITEM_ID);
    }

    public function setSku($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_SKU, $value);
    }

    public function getSku()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_SKU);
    }

    public function setSku2($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_SKU2, $value);
    }

    public function getSku2()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_SKU2);
    }

    public function setName($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_NAME, $value);
    }

    public function getName()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_NAME);
    }

    public function setSize($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_SIZE, $value);
    }

    public function getSize()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_SIZE);
    }

    public function setNetUnitPrice($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_NET_UNIT_PRICE, $value);
    }

    public function getNetUnitPrice()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_NET_UNIT_PRICE);
    }

    public function setNetAmount($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_NET_AMOUNT, $value);
    }

    public function getNetAmount()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_NET_AMOUNT);
    }

    public function setQty($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_QTY, $value);
    }

    public function getQty()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_QTY);
    }

    public function setStatus($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_STATUS, $value);
    }

    public function getStatus()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_STATUS);
    }

    public function getStatusName()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_STATUS_NAME);
    }

    public function setStatusName($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_STATUS_NAME, $value);
    }

    public function getQtyAccepted()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_QTY_ACCEPTED);
    }

    public function setQtyAccepted($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_QTY_ACCEPTED, $value);
    }

    public function getAmountAccepted()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_AMOUNT_ACCEPTED);
    }

    public function setAmountAccepted($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_AMOUNT_ACCEPTED, $value);
    }

    public function getQtyRejected()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_QTY_REJECTED);
    }

    public function setQtyRejected($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_QTY_REJECTED, $value);
    }

    public function getQtyExtra()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_QTY_EXTRA);
    }

    public function setQtyExtra($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_QTY_EXTRA, $value);
    }

    public function getQtyMissing()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_QTY_MISSING);
    }

    public function setQtyMissing($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_QTY_MISSING, $value);
    }

    public function getQtyReal()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_QTY_REAL);
    }

    public function setQtyReal($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_QTY_REAL, $value);
    }

    public function setReasonsCode($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_REASONS_CODE, $value);
    }

    public function getReasonsCode()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_REASONS_CODE);
    }

    public function setReasonsText($value)
    {
        $this->setData(SouthbayReturnProductItemInterfase::ENTITY_REASONS_TEXT, $value);
    }

    public function getReasonsText()
    {
        return $this->getData(SouthbayReturnProductItemInterfase::ENTITY_REASONS_TEXT);
    }
}
