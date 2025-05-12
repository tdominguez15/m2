<?php

namespace Southbay\Product\Model\Import;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductAttrLoader
{
    private $factory;
    private $management;
    private $repository;
    private $log;

    public function __construct(\Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $factory,
                                \Magento\Eav\Model\Entity\Attribute\OptionManagement  $management,
                                AttributeRepositoryInterface                          $repository,
                                \Psr\Log\LoggerInterface                              $log)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->management = $management;
        $this->log = $log;
    }

    public function load($options)
    {
        $result = [];

        foreach ($options as $key => $values) {
            $values = array_keys($values);
            $attribute = $this->findAttr($key);

            if (!$attribute) {
                $this->log->error('Product attribute not found: ' . $key);
                return false;
            }

            $result[$key] = [];
            $source = $attribute->getSource();

            foreach ($values as $value) {
                $_value = strtoupper(trim(strval($value)));
                $option_id = $source->getOptionId($_value);

                if (is_null($option_id)) {
                    $_option = $this->factory->create();
                    $_option->setValue($_value);
                    $_option->setLabel($_value);

                    $this->management->add($attribute->getEntityTypeId(), $attribute->getAttributeCode(), $_option);
                } else {
                    $_option = $this->factory->create();
                    $_option->setValue($option_id);
                    $_option->setLabel($_value);
                }

                $result[$key][$value] = $_option->getValue();
            }
        }

        return $result;
    }

    /**
     * @param $code
     * @return \Magento\Eav\Api\Data\AttributeInterface|null
     */
    public function findAttr($code)
    {
        try {
            $attribute = $this->repository->get(Product::ENTITY, $code);
        } catch (NoSuchEntityException $e) {
            $attribute = null;
        }

        return $attribute;
    }

    public function attrToMap($code)
    {
        $map = [];

        $attribute = $this->findAttr($code);

        if (!is_null($attribute)) {
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option) {
                $map[$option['value']] = $option['label'];
            }
        }

        return $map;
    }
}
