<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Form;

use Magento\Framework\Data\OptionSourceInterface;

class MagentoRolesOptionsProvider implements OptionSourceInterface
{
    private $context;
    private $collectionFactory;

    public function __construct(\Magento\Backend\Block\Template\Context                           $context,
                                \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $collectionFactory)
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
        $collection->addFieldToFilter('parent_id', \Magento\Authorization\Model\Acl\Role\Group::ROLE_TYPE);
        $collection->addOrder('role_name', 'ASC');
        $collection->load();

        $items = $collection->getItems();

        $result = [
            ['value' => '', 'label' => __('Seleccione un tipo de rol')]
        ];

        foreach ($items as $item) {
            $result[] = [
                'value' => $item->getData('role_id'),
                'label' => $item->getData('role_name')
            ];
        }

        return $result;
    }
}
