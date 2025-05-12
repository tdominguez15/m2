<?php

namespace Southbay\CustomCustomer\Api\Data;

interface OrderEntryNotificationConfigInterface
{
    const TABLE = 'southbay_order_entry_notification_config';
    const CACHE_TAG = 'southbay_order_entry_notification_config';
    const ENTITY_ID = 'entity_id';
    const ENTITY_COUNTRY_CODE = 'southbay_country_code';
    const ENTITY_FUNCTION_CODE = 'southbay_function_code';
    const ENTITY_TEMPLATE_ID = 'magento_template_id';
    const ENTITY_RETRY_AFTER = 'retry_after';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setCountryCode($countryCode);
    public function getCountryCode();

    public function setFunctionCode($functionCode);
    public function getFunctionCode();

    public function setTemplateId($templateId);
    public function getTemplateId();

    public function setRetryAfter($retryAfter);
    public function getRetryAfter();

    public function getCreatedAt(): string;

    public function setCreatedAt(string $createdAt);

    public function getUpdatedAt(): string;

    public function setUpdatedAt(string $updatedAt);
}
