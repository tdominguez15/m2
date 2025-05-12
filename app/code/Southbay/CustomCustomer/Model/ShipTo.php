<?php

namespace Southbay\CustomCustomer\Model;

use Magento\Framework\Model\AbstractModel;
use Southbay\CustomCustomer\Api\Data\ShipToInterface;

/**
 * Class ShipTo
 * @package Southbay\ShipTo\Model
 */
class ShipTo extends AbstractModel implements ShipToInterface
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Southbay\CustomCustomer\Model\ResourceModel\ShipTo::class);
    }

    /**
     * Get ship to ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_ID);
    }

    /**
     * Set ship to ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_ID, $id);
    }

    public function setName($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_NAME, $value);
    }

    public function getName()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_NAME);
    }

    public function getCustomerCode()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_CUSTOMER_CODE);
    }

    public function setCustomerCode($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_CUSTOMER_CODE, $value);
    }

    public function getCode()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_CODE);
    }

    public function setCode($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_CODE, $value);
    }

    public function getOldCode()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_OLD_CODE);
    }

    public function setOldCode($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_OLD_CODE, $value);
    }

    public function getAddress()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_ADDRESS);
    }

    public function setAddress($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_ADDRESS, $value);
    }

    public function getAddressNumber()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_ADDRESS_NUMBER);
    }

    public function setAddressNumber($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_ADDRESS_NUMBER, $value);
    }

    public function getState()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_STATE);
    }

    public function setState($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_STATE, $value);
    }

    public function getCountryCode()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_COUNTRY_CODE, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(ShipToInterface::CREATED_AT);
    }

    public function setCreatedAt($value)
    {
        return $this->setData(ShipToInterface::CREATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(ShipToInterface::UPDATED_AT);
    }

    public function setUpdatedAt($value)
    {
        return $this->setData(ShipToInterface::UPDATED_AT, $value);
    }

    public function getIsInternal()
    {
        return $this->getData(ShipToInterface::SOUTHBAY_SHIP_TO_IS_INTERNAL);
    }

    public function setIsInternal($value)
    {
        return $this->setData(ShipToInterface::SOUTHBAY_SHIP_TO_IS_INTERNAL, $value);
    }
}
