<?php

namespace Southbay\ReturnProduct\Block\Adminhtml;

abstract class PageGridBaseBlock extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'grid/view.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array                                 $data = []
    )
    {
        parent::__construct($context, $data);
    }

    protected function getButtonsProps()
    {
        return [];
    }

    protected function getGridBlockType()
    {
        return '';
    }


    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $buttons = $this->getButtonsProps();

        if (!empty($buttons)) {
            $this->buttonList->add('add_new', $buttons);
        }

        $this->setChild('grid', $this->getLayout()
            ->createBlock(
                $this->getGridBlockType(),
                'grid.view.grid')
        );

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    protected function _getAddButtonOptions()
    {
        return [];
    }

    /**
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl('southbay_return_product/*/new');
    }

    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
