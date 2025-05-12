<?php

namespace Southbay\Product\Model\ResourceModel\Segmentation;

use Magento\Framework\Data\OptionSourceInterface;

use Southbay\Product\Model\ResourceModel\Segmentation\CollectionFactory;

class OptionsProvider implements OptionSourceInterface
{
    private $options;
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        if (isset($this->options)) {
            return $this->options;
        }
        $this->options = [
            [
                'value' => '',
                'label' => __('Seleccione un tipo de código')
            ]
        ];

        $collection = $this->collectionFactory->create();
        foreach ($collection as $item) {
            $this->options[] = [
                'value' => $item->getData('code'),
                'label' => $item->getData('label')
            ];
        }

        return $this->options;
    }
}
