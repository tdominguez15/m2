<?php

namespace Southbay\ReturnProduct\Block\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

abstract class GridBaseBlock extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    protected $__collection = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data            $backendHelper,
        \Magento\Framework\Module\Manager       $moduleManager,
        array                                   $data = []
    )
    {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridGrid');

        $this->initDefaultSort();
        $this->initDefaultDir();
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('grid_record');
    }

    protected function _getCollection()
    {
        if (is_null($this->__collection)) {
            $entity = ObjectManager::getInstance()->get(
                EntityFactoryInterface::class
            );

            $this->__collection = new \Magento\Framework\Data\Collection($entity);
        }

        return $this->__collection;
    }

    protected function initDefaultSort()
    {
    }

    protected function initDefaultDir()
    {
        $this->setDefaultDir('ASC');
    }

    protected function initColumns()
    {
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        if (!is_null($this->_getCollection())) {
            $this->setCollection($this->_getCollection());
        }
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->initColumns();

        /*
        $block = $this->getLayout()->getBlock('grid.bottom.links');

        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        */

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('southbay_return_product/*/grid', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('southbay_return_product/*/edit', ['id' => $row->getId()]);
    }
}
