<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem as SouthbayReturnBalanceItemInterfase;

class SouthbayReturnBalanceItem extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnBalanceItemInterfase
{
    protected $_cacheTag = SouthbayReturnBalanceItemInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayReturnBalanceItemInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnBalanceItem');
    }

    public function getIdentities(): array
    {
        return [SouthbayReturnBalanceItemInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }


    public function getInvoiceId()
    {
        return $this->getData(SouthbayReturnBalanceItemInterfase::ENTITY_INVOICE_ID);
    }

    public function setInvoiceId($value)
    {
        $this->setData(SouthbayReturnBalanceItemInterfase::ENTITY_INVOICE_ID, $value);
    }

    public function getInvoiceItemId()
    {
        return $this->getData(SouthbayReturnBalanceItemInterfase::ENTITY_INVOICE_ITEM_ID);
    }

    public function setInvoiceItemId($value)
    {
        $this->setData(SouthbayReturnBalanceItemInterfase::ENTITY_INVOICE_ITEM_ID, $value);
    }

    public function getTotalInvoiced()
    {
        return $this->getData(SouthbayReturnBalanceItemInterfase::ENTITY_TOTAL_INVOICED);
    }

    public function setTotalInvoiced($value)
    {
        $this->setData(SouthbayReturnBalanceItemInterfase::ENTITY_TOTAL_INVOICED, $value);
    }

    public function getTotalReturn()
    {
        return $this->getData(SouthbayReturnBalanceItemInterfase::ENTITY_TOTAL_RETURN);
    }

    public function setTotalReturn($value)
    {
        $this->setData(SouthbayReturnBalanceItemInterfase::ENTITY_TOTAL_RETURN, $value);
    }

    public function getTotalAvailable()
    {
        return $this->getData(SouthbayReturnBalanceItemInterfase::ENTITY_TOTAL_AVAILABLE);
    }

    public function setTotalAvailable($value)
    {
        $this->setData(SouthbayReturnBalanceItemInterfase::ENTITY_TOTAL_AVAILABLE, $value);
    }

    public function setCreatedAt($value)
    {
        $this->setData(SouthbayReturnBalanceItemInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayReturnBalanceItemInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayReturnBalanceItemInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayReturnBalanceItemInterfase::ENTITY_UPDATED_AT);
    }
}
