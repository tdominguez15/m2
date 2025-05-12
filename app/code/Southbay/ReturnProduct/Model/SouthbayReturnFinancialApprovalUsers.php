<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApprovalUsers as ModelInterface;

class SouthbayReturnFinancialApprovalUsers extends \Magento\Framework\Model\AbstractModel implements ModelInterface
{
    protected $_cacheTag = ModelInterface::CACHE_TAG;

    protected $_eventPrefix = ModelInterface::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApprovalUsers');
    }

    public function getIdentities(): array
    {
        return [ModelInterface::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function setCreatedAt($value)
    {
        $this->setData(ModelInterface::ENTITY_CREATED_AT, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(ModelInterface::ENTITY_CREATED_AT);
    }

    public function setUpdatedAt($value)
    {
        $this->setData(ModelInterface::ENTITY_UPDATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(ModelInterface::ENTITY_UPDATED_AT);
    }

    public function getReturnId()
    {
        return $this->getData(ModelInterface::ENTITY_RETURN_ID);
    }

    public function setReturnId($value)
    {
        $this->setData(ModelInterface::ENTITY_RETURN_ID, $value);
    }

    public function getApproved()
    {
        return $this->getData(ModelInterface::ENTITY_APPROVED);
    }

    public function setApproved($value)
    {
        $this->setData(ModelInterface::ENTITY_APPROVED, $value);
    }

    public function getUserCode()
    {
        return $this->getData(ModelInterface::ENTITY_USER_CODE);
    }

    public function setUserCode($value)
    {
        $this->setData(ModelInterface::ENTITY_USER_CODE, $value);
    }

    public function getRolCode()
    {
        return $this->getData(ModelInterface::ENTITY_ROL_CODE);
    }

    public function setRolCode($value)
    {
        $this->setData(ModelInterface::ENTITY_ROL_CODE, $value);
    }

    public function getUserName()
    {
        return $this->getData(ModelInterface::ENTITY_USER_NAME);
    }

    public function setUserName($value)
    {
        $this->setData(ModelInterface::ENTITY_USER_NAME, $value);
    }
}
