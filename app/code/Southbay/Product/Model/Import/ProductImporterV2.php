<?php

namespace Southbay\Product\Model\Import;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductImporterV2
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    private $attributeFactory;

    private $attributeRepository;

    private $attributeSetRepository;

    private $_product_entity_type_id = null;
    private $_attribute_sets = null;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
                                \Magento\Eav\Api\AttributeRepositoryInterface             $attributeRepository,
                                \Magento\Eav\Api\AttributeSetRepositoryInterface          $attributeSetRepository)
    {
        $this->attributeFactory = $attributeFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    private function getObjectManager(): \Magento\Framework\App\ObjectManager
    {
        if (!$this->objectManager) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->objectManager;
    }

    public function import()
    {
        $this->setup();
        $this->read();
        $this->process();
    }

    private function read()
    {
    }

    private function process()
    {
    }

    private function setup()
    {
        $this->addAttributes();
        $this->createAttributeSet();
    }

    private function addAttributes()
    {
        $attributes = ProductImporterUtil::getAttributes();

        foreach ($attributes as $code => $config) {
            $_config = ProductImporterUtil::getDefaultAttributeConfig();
            if (!empty($config)) {
                $_config = array_merge($_config, $config);
            }

            $attribute = $this->findAttributeByCode($code);

            if (is_null($attribute)) {
                $_config['attribute_code'] = $code;

                $this->createProductAttribute($_config);
            }
        }
    }

    private function createAttributeSet()
    {
        $attr_set = $this->findAttributeSetByName(ProductImporterUtil::getAttributeSetName());

        $eavSetup = $this->getObjectManager()->get(\Magento\Eav\Setup\EavSetup::class);

        if (is_null($attr_set)) {
            $factory = $this->getObjectManager()->get(AttributeSetFactory::class);

            // Create a new attribute set
            $attributeSetData = [
                'attribute_set_name' => $this->getAttributeSetName(),
                'entity_type_id' => $this->getProductEntityTypeId(),
                'sort_order' => 10, // You can adjust the sort order as needed
            ];

            $defaultAttributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);;

            $attributeSet = $factory->create();
            $attributeSet->setData($attributeSetData);
            $attributeSet->validate();
            $attributeSet->save();
            $attributeSet->initFromSkeleton($defaultAttributeSetId);
            $attributeSet->save();
        }

        $attributes = array_keys($this->getAttributes());

        foreach ($attributes as $attributeCode) {
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);

            // Add the attribute to the attribute set
            $eavSetup->addAttributeToSet(
                Product::ENTITY,
                $attr_set->getId(), // $attributeSet['attribute_set_id'],
                // $eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSet['attribute_set_id']), $attributeId
                $eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attr_set->getId()), $attributeId
            );
        }
    }

    private function createProductAttribute($attributeData): void
    {
        $model = $this->attributeFactory->create();
        $model->addData($attributeData);
        $model->setEntityTypeId($this->getProductEntityTypeId());
        $model->save();
    }

    public function findAttributeByCode($code)
    {
        try {
            $attribute = $this->attributeRepository->get(Product::ENTITY, $code);
        } catch (NoSuchEntityException $e) {
            $attribute = null;
        }

        return $attribute;
    }

    private function getProductEntityTypeId()
    {
        if (is_null($this->_product_entity_type_id)) {
            $this->_product_entity_type_id = $this->getObjectManager()->create('Magento\Eav\Model\Entity')
                ->setType(Product::ENTITY)
                ->getTypeId();
        }
        return $this->_product_entity_type_id;
    }

    private function findAttributeSetByName($name)
    {
        $interface = $this->getObjectManager()->get(AttributeSetRepositoryInterface::class);
        $searchCriteriaBuilder = $this->getObjectManager()->get(SearchCriteriaBuilder::class);
        $filterBuilder = $this->getObjectManager()->get(FilterBuilder::class);

        $filters = [
            $filterBuilder
                ->setField('attribute_set_name')
                ->setConditionType('eq')
                ->setValue($name)
                ->create()
        ];

        $searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResults = $interface->getList($searchCriteria);

        $attributeSetItems = $searchResults->getItems();

        if (count($attributeSetItems) == 0) {
            return null;
        }

        $key = array_key_first($attributeSetItems);
        return $attributeSetItems[$key];
    }
}
