<?php

namespace Southbay\CustomCustomer\Api\Data;

interface OrderEntryNotificationInterface
{
    const TABLE = 'southbay_order_entry_notification';
    const ENTITY_ID = 'entity_id';
    const ENTITY_COUNTRY_CODE = 'southbay_country_code';
    const ENTITY_FUNCTION_CODE = 'southbay_function_code';
    const ENTITY_ORDER_ID = 'order_id';
    const ENTITY_INCREMENT_ID = 'increment_id';
    const ENTITY_TEMPLATE_ID = 'magento_template_id';
    const ENTITY_EMAIL = 'magento_user_email';
    const ENTITY_NAME = 'magento_user_name';
    const ENTITY_STATUS = 'status';
    const ENTITY_SEND_AT = 'send_at';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELLED = 'cancelled';

    public function setCountryCode($countryCode);

    public function getCountryCode();

    public function setFunctionCode($functionCode);

    public function getFunctionCode();

    public function setOrderId($orderId);

    public function getOrderId();

    public function setIncrementId($incrementId);

    public function getIncrementId();

    public function setTemplateId($templateId);

    public function getTemplateId();

    public function setEmail($email);

    public function getEmail();

    public function setName($name);

    public function getName();

    public function setStatus($status);

    public function getStatus();

    public function setSendAt($sendAt);

    public function getSendAt();

    public function getCreatedAt(): string;

    public function setCreatedAt(string $createdAt);

    public function getUpdatedAt(): string;

    public function setUpdatedAt(string $updatedAt);
}
