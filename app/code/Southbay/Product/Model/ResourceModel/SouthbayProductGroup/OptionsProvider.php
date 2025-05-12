<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductGroup;

use Magento\Framework\Data\OptionSourceInterface;
use Southbay\Product\Api\Data\SouthbayProductGroupInterface;

class OptionsProvider implements OptionSourceInterface
{
    private $options;

    public function toOptionArray()
    {
        if (isset($this->options)) {
            return $this->options;
        }

        $this->options = [
            [
                'value' => '',
                'label' => __('Seleccione una tipo de código')
            ],
            [
                'value' => SouthbayProductGroupInterface::TYPE_DEPARTMENT,
                'label' => __('Departamento')
            ],
            [
                'value' => SouthbayProductGroupInterface::TYPE_GENDER,
                'label' => __('Genero')
            ],
            [
                'value' => SouthbayProductGroupInterface::TYPE_AGE,
                'label' => __('Edad')
            ],
            [
                'value' => SouthbayProductGroupInterface::TYPE_SPORT,
                'label' => __('Deporte')
            ],
            [
                'value' => SouthbayProductGroupInterface::TYPE_SHAPE_1,
                'label' => __('Silueta')
            ],
            [
                'value' => SouthbayProductGroupInterface::TYPE_SHAPE_2,
                'label' => __('Característica')
            ]
        ];

        return $this->options;
    }
}
