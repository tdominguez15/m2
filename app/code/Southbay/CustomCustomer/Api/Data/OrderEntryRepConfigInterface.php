<?php

namespace Southbay\CustomCustomer\Api\Data;

interface OrderEntryRepConfigInterface
{
    const TABLE = 'southbay_order_entry_rep_config';
    const CACHE_TAG = 'southbay_order_entry_rep_config';
    const ENTITY_ID = 'entity_id';
    const ENTITY_USER_CODE = 'magento_user_code';
    const ENTITY_SOLD_TO_IDS = 'southbay_customer_config_sold_to_ids';
    const ENTITY_CAN_APPROVE_AT_ONCE = 'can_approve_at_once';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setUserCode(int $value);

    public function getUserCode(): int;

    public function getSoldToIds(): string;

    public function setSoldToIds(string $soldToIds);

    public function getCanApproveAtOnce(): bool;

    public function setCanApproveAtOnce(bool $canApproveAtOnce);

    public function getCreatedAt(): string;

    public function setCreatedAt(string $createdAt);

    public function getUpdatedAt(): string;

    public function setUpdatedAt(string $updatedAt);
}
