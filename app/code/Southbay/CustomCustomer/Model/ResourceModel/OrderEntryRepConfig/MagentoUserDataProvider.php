<?php

namespace Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;

class MagentoUserDataProvider implements OptionSourceInterface
{
    private $userCollectionFactory;
    private $_options;

    public function __construct(UserCollectionFactory $userCollectionFactory)
    {
        $this->userCollectionFactory = $userCollectionFactory;
    }

    public function toOptionArray()
    {
        if (isset($this->_options)) {
            return $this->_options;
        }

        $userCollection = $this->userCollectionFactory->create();
        $userCollection->addFieldToFilter('is_active', 1);
        $userCollection->load();

        $this->_options = [
            ['value' => '',
                'label' => __('Seleccione un usuario')
            ]
        ];

        foreach ($userCollection as $user) {
            $this->_options[] = [
                'value' => $user->getId(),
                'label' => $user->getUsername() . "(" . $user->getFirstname() . " " . $user->getLastname() . ")",
            ];
        }

        return $this->_options;
    }
}
