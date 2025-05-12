<?php

namespace Southbay\CustomCustomer\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Southbay\CustomCustomer\Api\Data\SouthbayMagentoCustomer;

class InstallData implements InstallDataInterface
{
    private $eavConfig;
    private $customerSetupFactory;
    private $attributeSetFactory;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        CustomerSetupFactory      $customerSetupFactory,
        AttributeSetFactory       $attributeSetFactory,
    )
    {
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $setup->endSetup();
    }
}
