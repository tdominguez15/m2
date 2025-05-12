<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\SoldTo;

use Magento\Framework\Data\OptionSourceInterface;

class SoldToSegmentationOptionsDataProvider implements OptionSourceInterface
{
    private $_options;


    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            $this->_options = [
                    [
                        'value' => 'NDDC',
                        'label' => 'NDDC - NIKE DIRECT DIGITAL COMMERCE'
                    ],
                    [
                        'value' => 'NSO',
                        'label' => 'NSO - NIKE STORE OWNED'
                    ],
                    [
                        'value' => 'NSP',
                        'label' => 'NSP - NIKE STORE PARTNER'
                    ],
                    [
                        'value' => 'SG',
                        'label' => 'SG - SPORTING GOODS'
                    ],
                    [
                        'value' => 'AS',
                        'label' => 'AS - ATHLETIC SPECIALTY'
                    ],
                    [
                        'value' => 'CS BKST',
                        'label' => 'CS BKST - CATEGORY SPECIALTY BASKET'
                    ],
                    [
                        'value' => 'CS RUN',
                        'label' => 'CS RUN - CATEGORY SPECIALTY RUNNING'
                    ],
                    [
                        'value' => 'CS NIKE SB',
                        'label' => 'CS NIKE SB - CATEGORY SPECIALTY NIKE SB'
                    ],
                    [
                        'value' => 'CS FTBL',
                        'label' => 'CS FTBL - CATEGORY SPECIALTY FUTBOL'
                    ],
                    [
                        'value' => 'NBHD',
                        'label' => 'NBHD - CATEGORY SPECIALTY NEIGHBORHOOD'
                    ],
                    [
                        'value' => 'MELI',
                        'label' => 'MELI - MERCADO LIBRE'
                    ],
                    [
                        'value' => 'NVS',
                        'label' => 'NVS - NIKE VALUE STORE'
                    ],
                    [
                        'value' => 'FRONTERA',
                        'label' => 'FRONTERA - FRONTERA'
                    ],
                    [
                        'value' => 'CS NBA',
                        'label' => 'CS NBA - CATEGORY SPECIALTY NBA'
                    ]
                ];
        }

        return $this->_options;
    }
}
