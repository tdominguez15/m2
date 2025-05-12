<?php

namespace Southbay\ReturnProduct\Api\Data;

interface SouthbaySapDoc
{
    const TABLE = 'southbay_sap_doc';
    const CACHE_TAG = self::TABLE;
    const ENTITY_ID = self::TABLE . '_id';

    const ENTITY_SAP_INTERFACE_ID = 'southbay_sap_interface_id';
    const ENTITY_TYPE_DOC = self::TABLE . '_type';
    const ENTITY_DOC_INTERNAL_NUMBER = self::TABLE . '_internal_number';
    const ENTITY_DOC_LEGAL_NUMBER = self::TABLE . '_legal_number';
    const ENTITY_TOTAL_NET_AMOUNT = self::TABLE . '_total_net_amount';
    const ENTITY_TOTAL_AMOUNT = self::TABLE . '_total_amount';

    const ENTITY_CREATED_AT = 'created_at';
    const ENTITY_UPDATED_AT = 'updated_at';

    public function setId($value);

    public function getId();

    public function getEntityId();

    public function setEntityId($entityId);

    public function getSapInterfaceId();
    public function setSapInterfaceId($value);

    public function getTypeDoc();
    public function setTypeDoc($value);

    public function getDocInternalNumber();
    public function setDocInternalNumber($value);

    public function getDocLegalNumber();
    public function setDocLegalNumber($value);

    public function getTotalNetAmount();
    public function setTotalNetAmount($value);

    public function getTotalAmount();
    public function setTotalAmount($value);

    public function setCreatedAt($value);

    public function getCreatedAt();

    public function setUpdatedAt($value);

    public function getUpdatedAt();

    public function getData($key = '', $index = null);
}
