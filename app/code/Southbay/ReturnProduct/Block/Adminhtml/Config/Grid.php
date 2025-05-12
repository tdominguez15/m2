<?php
namespace Southbay\ReturnProduct\Block\Adminhtml\Config;

use Southbay\ReturnProduct\Block\Adminhtml\PageGridBaseBlock;

class Grid extends PageGridBaseBlock {
    public function __construct(\Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    protected function getButtonsProps()
    {
        return [
            'id' => 'add_new_grid',
            'label' => __('Configurar'),
            'class' => 'action-primary',
            'class_name' => 'Magento\Backend\Block\Widget\Button',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
    }

    protected function getGridBlockType()
    {
        return 'Southbay\ReturnProduct\Block\Adminhtml\Config\Grid\Grid';
    }
}
