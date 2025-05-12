<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\ConfigStore;

use Magento\Framework\Data\OptionSourceInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;

class SouthbayFunctionOptionsDataProvider implements OptionSourceInterface
{
    private $_options;

    public function __construct()
    {
    }

    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            $this->_options = [
                ['value' => ConfigStoreInterface::FUNCTION_CODE_FUTURES, 'label' => __('Compras a futuro')],
                ['value' => ConfigStoreInterface::FUNCTION_CODE_AT_ONCE, 'label' => __('Compras at once')],
                ['value' => ConfigStoreInterface::FUNCTION_CODE_RTV, 'label' => __('Devoluciones')],
            ];
        }

        return $this->_options;
    }
}
