<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem as SouthbayReturnControlQaItemInterfase;

class SouthbayReturnControlQaItem extends \Magento\Framework\Model\AbstractModel implements SouthbayReturnControlQaItemInterfase
{
    protected $_cacheTag = SouthbayReturnControlQaItemInterfase::CACHE_TAG;

    protected $_eventPrefix = SouthbayReturnControlQaItemInterfase::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQaItem');
    }

    public function getIdentities(): array
    {
        return [SouthbayReturnControlQaItemInterfase::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }


    public function setCreatedAt($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_UPDATED_AT);
    }


    public function setReturnId($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_RETURN_ID, $value);
    }

    public function getReturnId()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_RETURN_ID);
    }

    public function setControlQaId($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_CONTROL_QA_ID, $value);
    }

    public function getControlQaId()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_CONTROL_QA_ID);
    }

    public function setQtyReject($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_REJECT, $value);
    }

    public function getQtyReject()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_REJECT);
    }

    public function setReasonCodes($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_REJECT_REASON_CODES, $value);
    }

    public function getReasonCodes()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_REJECT_REASON_CODES);
    }

    public function setReasonText($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_REJECT_REASON_TEXT, $value);
    }

    public function getReasonText()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_REJECT_REASON_TEXT);
    }

    public function setQtyReal($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_REAL, $value);
    }

    public function getQtyReal()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_REAL);
    }

    public function setQtyExtra($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_EXTRA, $value);
    }

    public function getQtyExtra()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_EXTRA);
    }

    public function setQtyAccepted($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_ACCEPTED, $value);
    }

    public function getQtyAccepted()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_ACCEPTED);
    }

    public function setQtyReturn($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_RETURN, $value);
    }

    public function getQtyReturn()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_RETURN);
    }

    public function setSku($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_SKU, $value);
    }

    public function getSku()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_SKU);
    }

    public function setSize($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_SIZE, $value);
    }

    public function getSize()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_SIZE);
    }

    public function setQtyMissing($value)
    {
        $this->setData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_MISSING, $value);
    }

    public function getQtyMissing()
    {
        return $this->getData(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_MISSING);
    }
}
