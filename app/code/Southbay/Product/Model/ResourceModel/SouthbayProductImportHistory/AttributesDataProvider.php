<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory;

use Magento\Framework\Data\OptionSourceInterface;
use Southbay\Product\Api\Data\SouthbayProduct;
use Southbay\Product\Model\Import\ProductAttrLoader;

class AttributesDataProvider implements OptionSourceInterface
{
    private $_options;
    private $productAttrLoader;
    private $log;

    public function __construct(ProductAttrLoader        $productAttrLoader,
                                \Psr\Log\LoggerInterface $log)
    {
        $this->productAttrLoader = $productAttrLoader;
        $this->log = $log;
    }

    public function toOptionArray()
    {
        if (isset($this->_options)) {
            return $this->_options;
        }

        $attributes = [
            SouthbayProduct::ENTITY_SEGMENTATION,
            SouthbayProduct::ENTITY_RELEASE_DATE,
            SouthbayProduct::ENTITY_COLOR,
            SouthbayProduct::ENTITY_GROUP,
            SouthbayProduct::ENTITY_PRICE,
            SouthbayProduct::ENTITY_PRICE_RETAIL,
            SouthbayProduct::ENTITY_PURCHASE_UNIT,
            SouthbayProduct::ENTITY_SOURCE,
            SouthbayProduct::ENTITY_NAME,
            SouthbayProduct::ENTITY_DESCRIPTION,
            SouthbayProduct::ENTITY_SKU_GENERIC,
            SouthbayProduct::ENTITY_SKU_VARIANT,
            SouthbayProduct::ENTITY_SKU,
            SouthbayProduct::ENTITY_EAN
        ];

        $this->_options = [];

        foreach ($attributes as $code) {
            $attr = $this->productAttrLoader->findAttr($code);
            if ($attr) {
                $this->_options[] = [
                    'value' => $code,
                    'label' => $attr->getDefaultFrontendLabel() ?? $code
                ];
            }
        }

        usort($this->_options, function ($option1, $option2) {
            return $option1['label'] <=> $option2['label'];
        });

        array_unshift($this->_options, ['value' => '', 'label' => __('Seleccione un atributo')]);

        return $this->_options;
    }
}
