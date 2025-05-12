<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Grid;

use Southbay\ReturnProduct\Block\Adminhtml\GridBaseBlock;

class Grid extends GridBaseBlock
{
    private $collection_factory;

    public function __construct(\Magento\Backend\Block\Template\Context                                                      $context,
                                \Magento\Backend\Helper\Data                                                                 $backendHelper,
                                \Magento\Framework\Module\Manager                                                            $moduleManager,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnConfigCollectionFactory $collection_factory,
                                array                                                                                        $data = [])
    {
        $this->collection_factory = $collection_factory;
        parent::__construct($context, $backendHelper, $moduleManager, $data);
    }

    protected function initDefaultSort()
    {
        $this->setDefaultSort(\Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_COUNTRY_CODE);
    }

    protected function initDefaultDir()
    {
        $this->setDefaultDir('ASC');
    }

    protected function _getCollection()
    {
        if (is_null($this->__collection)) {
            $this->__collection = $this->collection_factory->create();
        }

        return $this->__collection;
    }

    public function getFilterVisibility()
    {
        return false;
    }

    protected function initColumns()
    {
        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_COUNTRY_CODE,
            [
                'header' => __('País'),
                'type' => 'text',
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_COUNTRY_CODE,
                'renderer' => 'Southbay\ReturnProduct\Block\Adminhtml\Config\Grid\Column\CountryRenderer',
                'sortable' => true,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_TYPE,
            [
                'header' => __('Tipo'),
                'type' => 'text',
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_TYPE,
                'renderer' => 'Southbay\ReturnProduct\Block\Adminhtml\Config\Grid\Column\TypeRenderer',
                'sortable' => true,
                'filter' => false
            ]
        );

        $this->addColumn(
            'edit',
            [
                'header' => __(''),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Ver'),
                        'url' => [
                            'base' => '*/*/edit',
                        ],
                        'field' => 'id',
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );
    }
}
