<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Form\Grid;

use Magento\Framework\Data\OptionSourceInterface;

class ReturnProductClientOptionsProvider implements OptionSourceInterface
{
    private $context;
    private $collection;

    public function __construct(\Magento\Backend\Block\Template\Context                                                $context,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnProductCollection $collection)
    {
        $this->context = $context;
        $this->collection = $collection;
    }

    public function toOptionArray()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collection;
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                "main_table.southbay_return_customer_code",
                "main_table.southbay_return_customer_name"
            ]);
        $collection->distinct(true);
        $collection->setOrder('southbay_return_customer_name', 'ASC');
        $collection->load();

        $items = [];

        foreach ($collection as $item) {
            $items[] = [
                'value' => $item->getData('southbay_return_customer_code'),
                'label' => $item->getData('southbay_return_customer_name')
            ];
        }

        return $items;
    }
}
