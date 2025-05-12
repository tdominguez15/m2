<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;

class MagentoMailTemplateOptionsProvider implements OptionSourceInterface
{
    private $context;
    private $collectionFactory;

    public function __construct(\Magento\Backend\Block\Template\Context                       $context,
                                \Magento\Email\Model\ResourceModel\Template\CollectionFactory $collectionFactory)
    {
        $this->context = $context;
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addOrder('template_code', 'ASC');
        $collection->load();

        $items = $collection->getItems();

        $result = [
            ['value' => '', 'label' => __('Seleccione un tipo de rol')]
        ];

        foreach ($items as $item) {
            $result[] = [
                'value' => $item->getData('template_id'),
                'label' => $item->getData('template_code')
            ];
        }

        return $result;
    }
}
