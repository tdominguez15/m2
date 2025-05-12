<?php

namespace Southbay\Product\Model\ResourceModel\Season;

use Magento\Framework\Data\OptionSourceInterface;
use Southbay\Product\Api\Data\SeasonTypeInterface;

class OptionsProvider implements OptionSourceInterface
{
    private $context;
    private $collection_factory;
    private $options;

    private $season_type_collection_factory;

    public function __construct(\Magento\Backend\Block\Template\Context                            $context,
                                \Southbay\Product\Model\ResourceModel\Season\CollectionFactory     $collection_factory,
                                \Southbay\Product\Model\ResourceModel\SeasonType\CollectionFactory $season_type_collection_factory)
    {
        $this->context = $context;
        $this->collection_factory = $collection_factory;
        $this->season_type_collection_factory = $season_type_collection_factory;
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
                'label' => __('Seleccione una temporada')
            ]
        ];

        /**
         * @var \Southbay\Product\Model\Season $item
         */
        foreach ($items as $item) {
            $collection = $this->season_type_collection_factory->create();
            $collection->addFieldToFilter(SeasonTypeInterface::ENTITY_CODE, $item->getSeasonTypeCode());
            /**
             * @var SeasonTypeInterface $type
             */
            $type = $collection->getFirstItem();

            $this->options[] = [
                'value' => $item->getId(),
                'label' => $item->getSeasonName() . ' - ' . $item->getCountryCode() . ' (' . $type->getSeasonTypeCode() . ' - ' . $type->getSeasonTypeName() . ')'
            ];
        }

        return $this->options;
    }
}
