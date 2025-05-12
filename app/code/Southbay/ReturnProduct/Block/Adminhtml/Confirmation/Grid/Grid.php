<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Confirmation\Grid;

use Southbay\ReturnProduct\Block\Adminhtml\GridBaseBlock;

class Grid extends GridBaseBlock
{
    private $collection_factory;

    public function __construct(\Magento\Backend\Block\Template\Context                                                       $context,
                                \Magento\Backend\Helper\Data                                                                  $backendHelper,
                                \Magento\Framework\Module\Manager                                                             $moduleManager,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnProductCollectionFactory $collection_factory,
                                array                                                                                         $data = [])
    {
        $this->collection_factory = $collection_factory;
        parent::__construct($context, $backendHelper, $moduleManager, $data);
    }

    protected function initDefaultSort()
    {
        $this->setDefaultSort(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReject::ENTITY_COUNTRY_CODE);
    }

    protected function initDefaultDir()
    {
        $this->setDefaultDir('ASC');
    }

    protected function _getCollection()
    {
        if (is_null($this->__collection)) {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->collection_factory->create();
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS,
                ['eq' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL]);
            $this->__collection = $collection;
        }

        return $this->__collection;
    }


    public function getFilterVisibility()
    {
        return false;
    }

    protected function _prepareMassaction()
    {
        /**
         * @var \Magento\Backend\Block\Widget\Grid\Massaction\Extended $massaction
         */
        $massaction = $this->getMassactionBlock();
        $massaction->addItem('confirm', [
            'id' => 'confirm',
            'visible' => true,
            'label' => __('Confirmar'),
            'url' => $this->getConfirmURL()
        ]);

        return parent::_prepareMassaction();
    }

    protected function _prepareMassactionBlock()
    {
        $this->setNoFilterMassactionColumn(true);
        $this->setMassactionIdField(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_ID);

        return parent::_prepareMassactionBlock();
    }

    protected function initColumns()
    {
        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CREATED_AT,
            [
                'header' => __('Fecha de recepción'),
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CREATED_AT,
                'filter' => false
            ]
        );

        $this->addColumn(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_ID,
            [
                'header' => __('Nº Devolución'),
                'type' => 'text',
                'index' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_ID,
                'filter' => false
            ]
        );
    }

    /**
     * @return string
     */
    public function getConfirmURL()
    {
        return $this->getUrl('southbay_return_product/*/confirm', ['_current' => true]);
    }
}
