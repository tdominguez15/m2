<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\OrderEntryRepConfigInterface;

class OrderEntryRepConfig extends \Magento\Framework\Model\AbstractModel implements OrderEntryRepConfigInterface
{
    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function setUserCode(int $value)
    {
        return $this->setData(self::ENTITY_USER_CODE, $value);
    }

    public function getUserCode(): int
    {
        return $this->getData(self::ENTITY_USER_CODE);
    }

    public function getSoldToIds(): string
    {
        return $this->getData(self::ENTITY_SOLD_TO_IDS);
    }

    public function setSoldToIds(string $soldToIds)
    {
        return $this->setData(self::ENTITY_SOLD_TO_IDS, $soldToIds);
    }

    public function getCanApproveAtOnce(): bool
    {
        return $this->getData(self::ENTITY_CAN_APPROVE_AT_ONCE);
    }

    public function setCanApproveAtOnce(bool $canApproveAtOnce)
    {
        return $this->setData(self::ENTITY_CAN_APPROVE_AT_ONCE, $canApproveAtOnce);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt(string $createdAt)
    {
        return $this->setData(self::ENTITY_CREATED_AT, $createdAt);
    }

    public function getUpdatedAt(): string
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt(string $updatedAt)
    {
        return $this->setData(self::ENTITY_UPDATED_AT, $updatedAt);
    }
}
