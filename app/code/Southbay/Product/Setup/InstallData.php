<?php

namespace Southbay\Product\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Psr\Log\LoggerInterface;
use Southbay\Product\Model\Import\ProductImporter;

class InstallData implements InstallDataInterface
{
    private $eavConfig;
    private $customerSetupFactory;
    private $attributeSetFactory;

    private $log;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        CustomerSetupFactory      $customerSetupFactory,
        AttributeSetFactory       $attributeSetFactory,
        LoggerInterface           $log
    )
    {
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->log = $log;
    }


    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addCustomerAttrs($setup);
        $this->runProductSetup();
        $setup->endSetup();
    }

    private function runProductSetup()
    {
        $p = new ProductImporter();
        $p->runSetup();
    }

    private function addCustomerAttrs(ModuleDataSetupInterface $setup)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');

        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attr = $this->eavConfig->getAttribute(Customer::ENTITY, 'southbay_channel_level_list');

        if (is_null($attr) || !$attr->getId()) {
            $customerSetup->addAttribute(Customer::ENTITY, 'southbay_channel_level_list', [
                'type' => 'varchar',
                'label' => 'Canal y Nivel',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 1000,
                'system' => 0,
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ]);
        }

        $attr = $this->eavConfig->getAttribute(Customer::ENTITY, 'southbay_require_authorization');

        if (is_null($attr) || !$attr->getId()) {
            $customerSetup->addAttribute(Customer::ENTITY, 'southbay_require_authorization', [
                'type' => 'int',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'label' => 'Requiere Autorización',
                'input' => 'boolean',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 1002,
                'system' => 0,
                'default' => '1',
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ]);
        }
    }

}
