<?php

namespace Southbay\CustomCustomer\Model;

use Southbay\CustomCustomer\Api\Data\ShipToMapInterface;

class ShipToMap extends \Magento\Framework\Model\AbstractModel implements ShipToMapInterface
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Southbay\CustomCustomer\Model\ResourceModel\ShipToMap::class);
    }

    /**
     * Get ship to ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(ShipToMapInterface::ENTITY_ID);
    }

    /**
     * Set ship to ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(ShipToMapInterface::ENTITY_ID, $id);
    }

    public function setSoldToCode($soldToCode)
    {
        $this->setData(ShipToMapInterface::SOLD_TO_CODE, $soldToCode);
    }

    public function getSoldToCode()
    {
        return $this->getData(ShipToMapInterface::SOLD_TO_CODE);
    }

    public function setSoldToOldCode($soldToOldCode)
    {
        $this->setData(ShipToMapInterface::SOLD_TO_OLD_CODE, $soldToOldCode);
    }

    public function getSoldToOldCode()
    {
        return $this->getData(ShipToMapInterface::SOLD_TO_OLD_CODE);
    }

    public function setShipToCode($shipToCode)
    {
        $this->setData(ShipToMapInterface::SHIP_TO_CODE, $shipToCode);
    }

    public function getShipToCode()
    {
        return $this->getData(ShipToMapInterface::SHIP_TO_CODE);
    }

    public function setShipToOldCode($shipToOldCode)
    {
        $this->setData(ShipToMapInterface::SHIP_TO_OLD_CODE, $shipToOldCode);
    }

    public function getShipToOldCode()
    {
        return $this->getData(ShipToMapInterface::SHIP_TO_OLD_CODE);
    }
}
