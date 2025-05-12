<?php

namespace Southbay\ReturnProduct\Block\Frontend;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Southbay\CustomCustomer\Helper\SouthbayCustomerHelper;
use Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory as SoldToCollectionFactory;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory as ConfigStoreCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class NewReturnBlock extends \Magento\Framework\View\Element\Template
{
    private $customerSession;
    private $customerHelper;
    private $soldToCollectionFactory;
    private $configStoreCollectionFactory;
    private $storeManager;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        SouthbayCustomerHelper $customerHelper,
        SoldToCollectionFactory $soldToCollectionFactory,
        ConfigStoreCollectionFactory $configStoreCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerHelper = $customerHelper;
        $this->soldToCollectionFactory = $soldToCollectionFactory;
        $this->configStoreCollectionFactory = $configStoreCollectionFactory;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    public function getSoldToOptions()
    {
        $email = $this->customerSession->getCustomer()->getEmail();
        $config = $this->customerHelper->getConfigByEmail($email);

        if (is_null($config)) {
            return [];
        }

        $soldToIds = (!empty($config->getSoldToIds())) ? explode(',', $config->getSoldToIds()) : [];
        if (empty($soldToIds)) {
            return [];
        }

        $storeId = $this->storeManager->getStore()->getId();
        $configStore = $this->configStoreCollectionFactory->create()
            ->addFieldToFilter('southbay_store_code', ['eq' => $storeId])
            ->getFirstItem();

        $storeCountry = $configStore->getSouthbayCountryCode();

        $soldToCollection = $this->soldToCollectionFactory->create()
            ->addFieldToFilter('southbay_sold_to_country_code', ['eq' => $storeCountry])
            ->addFieldToFilter('southbay_sold_to_id', ['in' => $soldToIds]);

        $options = [];
        foreach ($soldToCollection as $soldTo) {
            $options[] = [
                'value' => $soldTo->getId(),
                'label' => $soldTo->getCustomerCode() . ' ' . $soldTo->getCustomerName() . ' (' . $storeCountry . ')'
            ];
        }

        return $options;
    }
}
