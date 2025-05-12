<?php
namespace Southbay\Product\Model;

use Magento\Framework\Model\AbstractModel;
use Southbay\Product\Api\Data\SegmentationInterface;

class Segmentation extends AbstractModel implements SegmentationInterface
{
    protected function _construct()
    {
        $this->_init(\Southbay\Product\Model\ResourceModel\Segmentation::class);
    }

    public function getId()
    {
        return $this->getData('entity_id');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    public function getLabel()
    {
        return $this->getData('label');
    }

    public function setLabel($label)
    {
        return $this->setData('label', $label);
    }
}
