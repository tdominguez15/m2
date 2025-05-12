<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbayReturnProduct
{
    const INVOICE_ORDER_DESC = 'DESC';
    const INVOICE_ORDER_DESC_NAME = 'Mayor a Menor';

    const INVOICE_ORDER_ASC = 'ASC';
    const INVOICE_ORDER_ASC_NAME = 'Menor a Mayor';

    const RETURN_TYPE_CODE_GOOD = 'good';
    const RETURN_TYPE_NAME_GOOD = 'Mercadería buena';

    const RETURN_TYPE_CODE_FAIL = 'fail';
    const RETURN_TYPE_NAME_FAIL = 'Mercadería fallada';

    const STATUS_CODE_GOOD_INIT = 'created_and_pending_approval';
    const STATUS_NAME_GOOD_INIT = 'Pendiente de Aprobación';
    const STATUS_CODE_FAIL_INIT = 'created';
    const STATUS_NAME_FAIL_INIT = 'Trámite creado';

    const STATUS_CODE_RECEIVED = 'received';
    const STATUS_NAME_RECEIVED = 'Trámite en cd';

    const STATUS_CODE_CANCEL = 'cancel';
    const STATUS_NAME_CANCEL = 'Cancelado';

    const STATUS_CODE_CANCEL_BY_ADMIN = 'cancel_admin';
    const STATUS_NAME_CANEL_BY_ADMIN = 'Cancelado por Southbay';

    const STATUS_CODE_CONTROL_QA = 'control_qa';
    const STATUS_NAME_CONTROL_QA = 'Trámite procesado';

    const STATUS_CODE_CONTROL_QA_GOOD = 'control_qa_good';
    const STATUS_NAME_CONTROL_QA_GOOD = 'Trámite controlado';

    const STATUS_CODE_REJECTED_IN_CONTROL_QA = 'rejected_in_control_qa';
    const STATUS_NAME_REJECTED_IN_CONTROL_QA = 'Rechazado en control de calidad';

    const STATUS_CODE_APPROVAL = 'approval';
    const STATUS_NAME_APPROVAL = 'Pendiente de acreditación';

    const STATUS_CODE_REJECTED = 'rejected';
    const STATUS_NAME_REJECTED = 'Trámite rechazado';

    const STATUS_CODE_APPROVAL_GOOD = 'approval_good';
    const STATUS_NAME_APPROVAL_GOOD = 'Trámite aprobado';

    const STATUS_CODE_CONFIRMED = 'confirmed';
    const STATUS_NAME_CONFIRMED = 'Trámite listo para acreditación';

    const STATUS_CODE_ARCHIVED = 'archived';
    const STATUS_NAME_ARCHIVED = 'Archivado';

    const STATUS_CODE_DOCUMENTS_SENT = 'documents_sent';
    const STATUS_NAME_DOCUMENTS_SENT = 'Solicitud de notas de crédito enviadas';

    const STATUS_CODE_CLOSED = 'closed';
    const STATUS_NAME_CLOSED = 'Tramite cerrado';

    const TYPE_ROL_CODE_RECEPTION = 'reception';
    const TYPE_ROL_NAME_RECEPTION = 'Recepcionista';

    const TYPE_ROL_CODE_APPROVAL = 'approval';
    const TYPE_ROL_NAME_APPROVAL = 'Aprobador';

    const TYPE_ROL_CODE_CONTROL_QA = 'control_qa';
    const TYPE_ROL_NAME_CONTROL_QA = 'Controlador';

    const TYPE_ROL_CODE_CHECK = 'checker';
    const TYPE_ROL_NAME_CHECK = 'Verificador';

    const TABLE = 'southbay_return';
    const CACHE_TAG = self::TABLE;

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'southbay_return_id';
    const ENTITY_COUNTRY_CODE = 'southbay_return_country_code';
    const ENTITY_TYPE = 'southbay_return_type';
    const ENTITY_CUSTOMER_CODE = 'southbay_return_customer_code';
    const ENTITY_CUSTOMER_NAME = 'southbay_return_customer_name';
    const ENTITY_USER_CODE = 'southbay_return_user_code';
    const ENTITY_USER_NAME = 'southbay_return_user_name';
    const ENTITY_USER_CONFIRM_CODE = 'southbay_return_user_confirm_code';
    const ENTITY_USER_CONFIRM_NAME = 'southbay_return_user_confirm_name';
    const ENTITY_CONFIRM_AT = 'southbay_return_confirm_at';
    const ENTITY_STATUS = 'southbay_return_status';
    const ENTITY_STATUS_NAME = 'southbay_return_status_name';
    const ENTITY_TOTAL_RETURN = 'southbay_return_total_qty';
    const ENTITY_TOTAL_AMOUNT = 'southbay_return_total_amount';
    const ENTITY_TOTAL_ACCEPTED = 'southbay_return_total_accepted';
    const ENTITY_TOTAL_AMOUNT_ACCEPTED = 'southbay_return_total_amount_accepted';
    const ENTITY_TOTAL_REJECTED = 'southbay_return_rejected';
    const ENTITY_TOTAL_AMOUNT_REJECTED = 'southbay_return_amount_rejected';
    const ENTITY_PRINTED = 'southbay_return_printed';
    const ENTITY_PRINTED_AT = 'southbay_return_printed_at';
    const ENTITY_LABEL_TOTAL_PACKAGES = 'southbay_return_total_packages';
    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function setCountryCode($value);
    public function getCountryCode();

    public function getType();

    public function setType($value);

    public function getCustomerCode();

    public function setCustomerCode($value);

    public function getCustomerName();

    public function setCustomerName($value);

    public function getUserCode();

    public function setUserCode($value);

    public function getUserName();

    public function setUserName($value);

    public function getUserConfirmCode();

    public function setUserConfirmCode($value);

    public function getUserConfirmName();

    public function setUserConfirmName($value);

    public function getStatus();

    public function setStatus($value);

    public function getStatusName();

    public function setStatusName($value);

    public function getTotalReturn();

    public function setTotalReturn($value);

    public function getTotalAmount();

    public function setTotalAmount($value);

    public function getTotalAccepted();

    public function setTotalAccepted($value);

    public function getTotalAmountAccepted();

    public function setTotalAmountAccepted($value);

    public function getTotalRejected();

    public function setTotalRejected($value);

    public function getTotalAmountRejected();

    public function setTotalAmountRejected($value);

    public function isPrinted();

    public function setPrinted($value);

    public function getPrintedAt();

    public function setPrintedAt($value);

    public function getLabelTotalPackages();

    public function setLabelTotalPackages($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
