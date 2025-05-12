<?php

namespace Southbay\Product\Model;

class SouthbaySapProduct extends \Magento\Framework\Model\AbstractModel implements \Southbay\Product\Api\Data\SouthbaySapProductInterface
{
    protected $_cacheTag = false;

    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Southbay\Product\Model\ResourceModel\SouthbaySapProduct');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::ENTITY_CREATED_AT);
    }

    public function setCreatedAt($date)
    {
        $this->setData(self::ENTITY_CREATED_AT, $date);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::ENTITY_UPDATED_AT);
    }

    public function setUpdatedAt($date)
    {
        $this->setData(self::ENTITY_UPDATED_AT, $date);
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($value)
    {
        $this->setData(self::ENTITY_ID, $value);
    }

    public function getCountryCode()
    {
        return $this->getData(self::ENTITY_COUNTRY_CODE);
    }

    public function setCountryCode($value)
    {
        $this->setData(self::ENTITY_COUNTRY_CODE, $value);
    }

    public function getSapCountryCode()
    {
        return $this->getData(self::ENTITY_SAP_COUNTRY_CODE);
    }

    public function setSapCountryCode($value)
    {
        $this->setData(self::ENTITY_SAP_COUNTRY_CODE, $value);
    }

    public function getMagentoProductId()
    {
        return $this->getData(self::ENTITY_MAGENTO_PRODUCT_ID);
    }

    public function setMagentoProductId($value)
    {
        $this->setData(self::ENTITY_MAGENTO_PRODUCT_ID, $value);
    }

    public function getSku()
    {
        return $this->getData(self::ENTITY_SKU);
    }

    public function setSku($value)
    {
        $this->setData(self::ENTITY_SKU, $value);
    }

    public function getSkuGeneric()
    {
        return $this->getData(self::ENTITY_SKU_GENERIC);
    }

    public function setSkuGeneric($value)
    {
        $this->setData(self::ENTITY_SKU_GENERIC, $value);
    }

    public function getSkuVariant()
    {
        return $this->getData(self::ENTITY_SKU_VARIANT);
    }

    public function setSkuVariant($value)
    {
        $this->setData(self::ENTITY_SKU_VARIANT, $value);
    }

    public function getSkuFull()
    {
        return $this->getData(self::ENTITY_SKU_FULL);
    }

    public function setSkuFull($value)
    {
        $this->setData(self::ENTITY_SKU_FULL, $value);
    }

    public function getName()
    {
        return $this->getData(self::ENTITY_NAME);
    }

    public function setName($value)
    {
        $this->setData(self::ENTITY_NAME, $value);
    }

    public function getColor()
    {
        return $this->getData(self::ENTITY_COLOR);
    }

    public function setColor($value)
    {
        $this->setData(self::ENTITY_COLOR, $value);
    }

    public function getSize()
    {
        return $this->getData(self::ENTITY_SIZE);
    }

    public function setSize($value)
    {
        $this->setData(self::ENTITY_SIZE, $value);
    }

    public function getEan()
    {
        return $this->getData(self::ENTITY_EAN);
    }

    public function setEan($value)
    {
        $this->setData(self::ENTITY_EAN, $value);
    }

    public function getGroupCode()
    {
        return $this->getData(self::ENTITY_GROUP_CODE);
    }

    public function setGroupCode($value)
    {
        $this->setData(self::ENTITY_GROUP_CODE, $value);
    }

    public function getGroupName()
    {
        return $this->getData(self::ENTITY_GROUP_NAME);
    }

    public function setGroupName($value)
    {
        $this->setData(self::ENTITY_GROUP_NAME, $value);
    }

    public function getSeasonName()
    {
        return $this->getData(self::ENTITY_SEASON_NAME);
    }

    public function setSeasonName($value)
    {
        $this->setData(self::ENTITY_SEASON_NAME, $value);
    }

    public function getSeasonYear()
    {
        return $this->getData(self::ENTITY_SEASON_YEAR);
    }

    public function setSeasonYear($value)
    {
        $this->setData(self::ENTITY_SEASON_YEAR, $value);
    }

    public function getBu()
    {
        return $this->getData(self::ENTITY_BU);
    }

    public function setBu($value)
    {
        $this->setData(self::ENTITY_BU, $value);
    }

    public function getGender()
    {
        return $this->getData(self::ENTITY_GENDER);
    }

    public function setGender($value)
    {
        $this->setData(self::ENTITY_GENDER, $value);
    }

    public function getAge()
    {
        return $this->getData(self::ENTITY_AGE);
    }

    public function setAge($value)
    {
        $this->setData(self::ENTITY_AGE, $value);
    }

    public function getSport()
    {
        return $this->getData(self::ENTITY_SPORT);
    }

    public function setSport($value)
    {
        $this->setData(self::ENTITY_SPORT, $value);
    }

    public function getShape1()
    {
        return $this->getData(self::ENTITY_SHAPE_1);
    }

    public function setShape1($value)
    {
        $this->setData(self::ENTITY_SHAPE_1, $value);
    }

    public function getShape2()
    {
        return $this->getData(self::ENTITY_SHAPE_2);
    }

    public function setShape2($value)
    {
        $this->setData(self::ENTITY_SHAPE_2, $value);
    }

    public function getBrand()
    {
        return $this->getData(self::ENTITY_BRAND);
    }

    public function setBrand($value)
    {
        $this->setData(self::ENTITY_BRAND, $value);
    }

    public function getChannel()
    {
        return $this->getData(self::ENTITY_CHANNEL);
    }

    public function setChannel($value)
    {
        $this->setData(self::ENTITY_CHANNEL, $value);
    }

    public function getLevel()
    {
        return $this->getData(self::ENTITY_LEVEL);
    }

    public function setLevel($value)
    {
        $this->setData(self::ENTITY_LEVEL, $value);
    }

    public function getPrice()
    {
        return $this->getData(self::ENTITY_PRICE);
    }

    public function setPrice($value)
    {
        $this->setData(self::ENTITY_PRICE, $value);
    }

    public function getSuggestedRetailPrice()
    {
        return $this->getData(self::ENTITY_SUGGESTED_RETAIL_PRICE);
    }

    public function setSuggestedRetailPrice($value)
    {
        $this->setData(self::ENTITY_SUGGESTED_RETAIL_PRICE, $value);
    }

    public function getDenomination()
    {
        return $this->getData(self::ENTITY_DENOMINATION);
    }

    public function setDenomination($value)
    {
        $this->setData(self::ENTITY_DENOMINATION, $value);
    }

    public function getSaleDateFrom()
    {
        return $this->getData(self::ENTITY_SALE_DATE_FROM);
    }

    public function setSaleDateFrom($value)
    {
        $this->setData(self::ENTITY_SALE_DATE_FROM, $value);
    }

    public function getSaleDateTo()
    {
        return $this->getData(self::ENTITY_SALE_DATE_TO);
    }

    public function setSaleDateTo($value)
    {
        $this->setData(self::ENTITY_SALE_DATE_TO, $value);
    }
}
