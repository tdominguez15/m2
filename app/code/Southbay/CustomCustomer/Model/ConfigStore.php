<?php
namespace Southbay\CustomCustomer\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ConfigStore
 * @package Southbay\StoreConfiguration\Model
 */
class ConfigStore extends AbstractModel
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Southbay\CustomCustomer\Model\ResourceModel\ConfigStore::class);
    }

    /**
     * Get configuration ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_getData('southbay_general_config_id');
    }


    /**
     * Get function code.
     *
     * @return string|null
     */
    public function getFunctionCode()
    {
        return $this->_getData('southbay_function_code');
    }

    /**
     * Set function code.
     *
     * @param string $functionCode
     * @return $this
     */
    public function setFunctionCode($functionCode)
    {
        return $this->setData('southbay_function_code', $functionCode);
    }

    /**
     * Get country code.
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->_getData('southbay_country_code');
    }

    /**
     * Set country code.
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        return $this->setData('southbay_country_code', $countryCode);
    }

    /**
     * Get store code.
     *
     * @return int|null
     */
    public function getStoreCode()
    {
        return $this->_getData('southbay_store_code');
    }

    /**
     * Set store code.
     *
     * @param int $storeCode
     * @return $this
     */
    public function setStoreCode($storeCode)
    {
        return $this->setData('southbay_store_code', $storeCode);
    }

    /**
     * Get creation time.
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_getData('created_at');
    }

    /**
     * Set creation time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData('created_at', $createdAt);
    }

    /**
     * Get update time.
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_getData('updated_at');
    }

    /**
     * Set update time.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData('updated_at', $updatedAt);
    }
}
