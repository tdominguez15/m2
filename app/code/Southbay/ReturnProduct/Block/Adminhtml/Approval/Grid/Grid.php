<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Approval\Grid;

use Southbay\ReturnProduct\Block\Adminhtml\GridBaseBlock;

class Grid extends GridBaseBlock
{
    private $collection_factory;
    private $helper;

    public function __construct(\Magento\Backend\Block\Template\Context                                                                 $context,
                                \Magento\Backend\Helper\Data                                                                            $backendHelper,
                                \Southbay\ReturnProduct\Helper\Data                                                                     $helper,
                                \Magento\Framework\Module\Manager                                                                       $moduleManager,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalCollectionFactory $collection_factory,
                                array                                                                                                   $data = [])
    {
        $this->collection_factory = $collection_factory;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $moduleManager, $data);
    }

    protected function initDefaultSort()
    {
        $this->setDefaultSort(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_CREATED_AT);
    }

    protected function initDefaultDir()
    {
        $this->setDefaultDir('DESC');
    }

    protected function _getCollection()
    {
        if (is_null($this->__collection)) {
            $values = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);

            if (empty($values)) {
                $values = ['-'];
            }

            $collection = $this->collection_factory->create();
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_COUNTRY_CODE,
                ['in' => $values]
            );

            $this->__collection = $collection;
        }

        return $this->__collection;
    }

    protected function initColumns()
    {
        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_CREATED_AT,
            [
                'header' => __('Fecha de aprobación'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_CREATED_AT,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_RETURN_ID,
            [
                'header' => __('Nº Devolución'),
                'type' => 'text',
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_RETURN_ID,
                'filter' => false
            ]
        );

        $this->addColumn(
            'automatic',
            [
                'header' => __('Aprobado automaticamente'),
                'renderer' => 'Southbay\ReturnProduct\Block\Adminhtml\Approval\Grid\Column\AutomaticRenderer',
                'index' => 'automatic',
                'sortable' => false,
                'filter' => false
            ]
        );


        $this->addColumn(
            'pending_approvals_users',
            [
                'header' => __('Aprobaciones pendientes'),
                'renderer' => 'Southbay\ReturnProduct\Block\Adminhtml\Approval\Grid\Column\PendingApprovalUsersRenderer',
                'index' => 'pending_approvals_users',
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            'approvals_users',
            [
                'header' => __('Respuesta de los aprobadores'),
                'renderer' => 'Southbay\ReturnProduct\Block\Adminhtml\Approval\Grid\Column\ApprovalUsersRenderer',
                'index' => 'approvals_users',
                'sortable' => false,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_TOTAL_ACCEPTED,
            [
                'header' => __('Total Aceptado'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_TOTAL_ACCEPTED,
                'sortable' => false,
                'filter' => false
            ]
        );


        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_TOTAL_ACCEPTED_AMOUNT,
            [
                'header' => __('Monto aceptado'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_TOTAL_ACCEPTED_AMOUNT,
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
