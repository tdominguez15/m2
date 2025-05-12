<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\ControlQa\Grid;

use Southbay\ReturnProduct\Block\Adminhtml\GridBaseBlock;

class Grid extends GridBaseBlock
{
    private $collection_factory;

    public function __construct(\Magento\Backend\Block\Template\Context                                                         $context,
                                \Magento\Backend\Helper\Data                                                                    $backendHelper,
                                \Magento\Framework\Module\Manager                                                               $moduleManager,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaCollectionFactory $collection_factory,
                                array                                                                                           $data = [])
    {
        $this->collection_factory = $collection_factory;
        parent::__construct($context, $backendHelper, $moduleManager, $data);
    }

    protected function initDefaultSort()
    {
        $this->setDefaultSort(\Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_CREATED_AT);
    }

    protected function initDefaultDir()
    {
        $this->setDefaultDir('DESC');
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
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_CREATED_AT,
            [
                'header' => __('Fecha de control'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_CREATED_AT,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_RETURN_ID,
            [
                'header' => __('Nº Devolución'),
                'type' => 'text',
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_RETURN_ID,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_TYPE,
            [
                'header' => __('Tipo'),
                'type' => 'text',
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_TYPE,
                'renderer' => 'Southbay\ReturnProduct\Block\Adminhtml\Reception\Grid\Column\CustomRenderer',
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CUSTOMER_NAME,
            [
                'header' => __('Cliente'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CUSTOMER_NAME,
                'renderer' => 'Southbay\ReturnProduct\Block\Adminhtml\Reception\Grid\Column\CustomRenderer',
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_REAL,
            [
                'header' => __('Total Real'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_REAL,
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_MISSING,
            [
                'header' => __('Total Faltante'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_MISSING,
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_EXTRA,
            [
                'header' => __('Total Sobrante'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_EXTRA,
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_ACCEPTED,
            [
                'header' => __('Total Aceptado'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_ACCEPTED,
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_REJECT,
            [
                'header' => __('Total Rechazado'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_TOTAL_REJECT,
                'sortable' => false,
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
