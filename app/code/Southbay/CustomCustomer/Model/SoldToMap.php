<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\SoldToMapInterface;

class SoldToMap extends \Magento\Framework\Model\AbstractModel implements SoldToMapInterface
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Southbay\CustomCustomer\Model\ResourceModel\SoldToMap::class);
    }

    /**
     * Get ship to ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(SoldToMapInterface::ENTITY_ID);
    }

    /**
     * Set ship to ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(SoldToMapInterface::ENTITY_ID, $id);
    }

    public function setSoldToCode($soldToCode)
    {
        $this->setData(SoldToMapInterface::SOLD_TO_CODE, $soldToCode);
    }

    public function getSoldToCode()
    {
        return $this->getData(SoldToMapInterface::SOLD_TO_CODE);
    }

    public function setSoldToOldCode($soldToOldCode)
    {
        $this->setData(SoldToMapInterface::SOLD_TO_OLD_CODE, $soldToOldCode);
    }

    public function getSoldToOldCode()
    {
        return $this->getData(SoldToMapInterface::SOLD_TO_OLD_CODE);
    }
}
