<?php

namespace Southbay\Product\Model\ResourceModel\SeasonType;

use Magento\Framework\Data\OptionSourceInterface;

class OptionsProvider implements OptionSourceInterface
{
    private $context;
    private $collection_factory;
    private $options;

    public function __construct(\Magento\Backend\Block\Template\Context                            $context,
                                \Southbay\Product\Model\ResourceModel\SeasonType\CollectionFactory $collection_factory)
    {
        $this->context = $context;
        $this->collection_factory = $collection_factory;
    }

    public function toOptionArray()
    {
        if (isset($this->options)) {
            return $this->options;
        }

        $collection = $this->collection_factory->create();
        $collection->load();
        $items = $collection->getItems();

        $this->options = [
            [
                'value' => '',
                'label' => __('Seleccione un tipo')
            ]
        ];

        /**
         * @var \Southbay\Product\Model\SeasonType $item
         */
        foreach ($items as $item) {
            $this->options[] = [
                'value' => $item->getSeasonTypeCode(),
                'label' => $item->getSeasonTypeCode() . ' - ' . $item->getSeasonTypeName()
            ];
        }

        return $this->options;
    }
}
