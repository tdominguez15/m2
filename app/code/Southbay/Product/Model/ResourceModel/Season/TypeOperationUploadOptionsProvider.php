<?php

namespace Southbay\Product\Model\ResourceModel\Season;

use Magento\Framework\Data\OptionSourceInterface;

class TypeOperationUploadOptionsProvider implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'progressive',
                'label' => __('Cargar variantes')
            ],
            [
                'value' => 'replace',
                'label' => __('Reemplazar variantes')
            ]
        ];
    }
}
