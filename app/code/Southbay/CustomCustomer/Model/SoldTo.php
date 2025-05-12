<?php
namespace Southbay\CustomCustomer\Model;

use Magento\Framework\Model\AbstractModel;
use Southbay\CustomCustomer\Api\Data\SoldToInterface;

/**
 * Class SoldTo
 * @package Southbay\SoldTo\Model
 */
class SoldTo extends AbstractModel implements SoldToInterface
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Southbay\CustomCustomer\Model\ResourceModel\SoldTo::class);
    }
    /**
     * Get ship to ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_ID);
    }

    /**
     * Set ship to ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_ID, $id);
    }

    public function getCountryCode()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_COUNTRY_CODE, $value);
    }

    public function getSapCountryCode()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_SAP_COUNTRY_CODE);
    }

    public function setSapCountryCode($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_SAP_COUNTRY_CODE, $value);
    }

    public function getChannelCode()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_CHANNEL_CODE);
    }

    public function setChannelCode($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_CHANNEL_CODE, $value);
    }

    public function getSectionCode()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_SECTION_CODE);
    }

    public function setSectionCode($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_SECTION_CODE, $value);
    }

    public function getCustomerCode()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_CODE);
    }

    public function setCustomerCode($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_CODE, $value);
    }

    public function getOldCustomerCode()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_OLD_CUSTOMER_CODE);
    }

    public function setOldCustomerCode($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_OLD_CUSTOMER_CODE, $value);
    }

    public function getCountryBusinessCode()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_COUNTRY_BUSINESS_CODE);
    }

    public function setCountryBusinessCode($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_COUNTRY_BUSINESS_CODE, $value);
    }

    public function getCustomerName()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_NAME);
    }

    public function setCustomerName($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_CUSTOMER_NAME, $value);
    }

    public function getLocked()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_LOCKED);
    }

    public function setLocked($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_LOCKED, $value);
    }

    public function getAutomaticallyAuthorizePurchases()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SOLD_TO_AUTOMATICALLY_AUTHORIZE_PURCHASES);
    }

    public function setAutomaticallyAuthorizePurchases($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SOLD_TO_AUTOMATICALLY_AUTHORIZE_PURCHASES, $value);
    }

    public function getSegmentation()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_SEGMENTATION);
    }

    public function setSegmentation($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_SEGMENTATION, $value);
    }

    public function getIsInternal()
    {
        return $this->getData(SoldToInterface::SOUTHBAY_IS_INTERNAL);
    }

    public function setIsInternal($value)
    {
        return $this->setData(SoldToInterface::SOUTHBAY_IS_INTERNAL, $value);
    }
}
