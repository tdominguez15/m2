<?php

namespace Southbay\Product\Model\Import;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\Product\Api\Data\SouthbayProductGroupInterface;
use Southbay\Product\Model\Season;

class SubcategoriesLoader
{

    private $categoryRepository;

    private $categoryCollectionFactory;

    private $productGroupCollectionFactory;

    private $storeManager;

    private $log;

    public $last_error;

    public function __construct(CategoryRepository                                                           $categoryRepository,
                                \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory              $categoryCollectionFactory,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductGroup\CollectionFactory $productGroupCollectionFactory,
                                StoreManagerInterface                                                        $storeManager,
                                \Psr\Log\LoggerInterface                                                     $log)
    {
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productGroupCollectionFactory = $productGroupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->log = $log;
    }

    public function loadFromSeason($items, Season $season)
    {
        /**
         * @var \Magento\Catalog\Model\Category $category_parent
         */
        $category_parent = $this->categoryRepository->get($season->getSeasonCategoryId());

        if (is_null($category_parent->getId())) {
            $this->log->error('Season category not found', ['season_id' => $season->getId()]);
            return false;
        }

        return $this->load($items, $category_parent);
    }

    public function loadFromStore($items, $store_id)
    {
        /**
         * @var \Magento\Store\Model\Store $store
         */
        $store = $this->storeManager->getStore($store_id);
        $group = $store->getGroup();

        /**
         * @var \Magento\Catalog\Model\Category $category_parent
         */
        $category_parent = $this->categoryRepository->get($group->getRootCategoryId());

        if (is_null($category_parent->getId())) {
            $this->log->error('Category root not found', ['store_id' => $store->getId(), 'name' => $store->getName()]);
            return false;
        }

        return $this->load($items, $category_parent);
    }

    /**
     * @param array $items
     * @param \Magento\Catalog\Model\Category $category_parent
     * @return array|false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function load($items, $category_parent)
    {
        $result = [];
        $map = [];

        foreach ($items as $key => $levels) {
            $url_key = strtolower($category_parent->getName());
            $level_1_result = $this->createSubCategoryByLevel($levels, 'level_1', $key, $url_key, $category_parent, $map);

            if (!$level_1_result) {
                return false;
            }

            $url_key .= '/' . strtolower($level_1_result['group_name']);
            $level_2_result = $this->createSubCategoryByLevel($levels, 'level_2', $key, $url_key, $level_1_result['category'], $map);

            if (!$level_2_result) {
                return false;
            }

            $url_key .= '/' . strtolower($level_2_result['group_name']);
            $level_3_result = $this->createSubCategoryByLevel($levels, 'level_3', $key, $url_key, $level_2_result['category'], $map);

            if (!$level_3_result) {
                return false;
            }

            $url_key .= '/' . strtolower($level_3_result['group_name']);
            $level_4_result = $this->createSubCategoryByLevel($levels, 'level_4', $key, $url_key, $level_3_result['category'], $map);

            if (!$level_4_result) {
                return false;
            }

            $url_key .= '/' . strtolower($level_4_result['group_name']);
            $level_5_result = $this->createSubCategoryByLevel($levels, 'level_5', $key, $url_key, $level_4_result['category'], $map);

            if (!$level_5_result) {
                return false;
            }

            $url_key .= '/' . strtolower($level_5_result['group_name']);
            $level_6_result = $this->createSubCategoryByLevel($levels, 'level_6', $key, $url_key, $level_5_result['category'], $map);

            if (!$level_6_result) {
                return false;
            }

            $result[$key] = [
                'ids' => [
                    $level_1_result['category_id'],
                    $level_2_result['category_id'],
                    $level_3_result['category_id'],
                    $level_4_result['category_id'],
                    $level_5_result['category_id'],
                    $level_6_result['category_id']
                ]
            ];

            $result[$key][$level_1_result['type']] = $level_1_result['group_name'];
            $result[$key][$level_2_result['type']] = $level_2_result['group_name'];
            $result[$key][$level_3_result['type']] = $level_3_result['group_name'];
            $result[$key][$level_4_result['type']] = $level_4_result['group_name'];
            $result[$key][$level_5_result['type']] = $level_5_result['group_name'];
            $result[$key][$level_6_result['type']] = $level_6_result['group_name'];
        }

        return $result;
    }

    private function createSubCategoryByLevel($levels, $level_code, $parent_key, $url_key, $category_parent, &$map)
    {
        $hash = $levels[$level_code]['code'] . '-' . $levels[$level_code]['type'] . '-' . $level_code . '-' . $parent_key;

        if (!isset($map[$hash])) {
            $category_result = $this->_createSubCategoryByLevel($levels[$level_code], $level_code, $url_key, $parent_key, $levels, $category_parent);

            if ($category_result === false) {
                return false;
            }

            $map[$hash] = $category_result;
        }

        return $map[$hash];
    }

    private function _createSubCategoryByLevel($level, $level_code, $url_key, $parent_key, $levels, \Magento\Catalog\Model\Category $category_parent)
    {
        $group = $this->findProductGroupByCode($level['code'], $level['type']);

        if (is_null($group)) {
            $this->log->error('Product group not found', ['level' => $level['code'], 'type' => $level['type'], 'parent_key' => $parent_key, 'levels' => $levels]);
            $this->last_error = __('Grupo de producto no encontrado: %1. Tipo: %2. Grupo: %3', $level['code'], $level['type'], $parent_key);
            return false;
        }

        $category = $this->createSubcategory($group, $level_code, $url_key, $category_parent);

        return [
            'category' => $category,
            'category_id' => $category->getId(),
            'group' => $group,
            'type' => $level['type'],
            'group_name' => $category->getName()
        ];
    }

    private function createSubcategory(SouthbayProductGroupInterface $group, $level_code, $url_key, \Magento\Catalog\Model\Category $category_parent)
    {
        $collection = $this->categoryCollectionFactory->create();

        $name = $group->getName();
        $position = null;

        if ($level_code == 1) {
            if ($group->getCode() == 'V') {
                $position = 1;
            } else if ($group->getCode() == 'W') {
                $position = 2;
            } else if ($group->getCode() == 'X') {
                $position = 3;
            } else if ($group->getCode() == 'Y') {
                $position = 4;
            } else if ($group->getCode() == 'Z') {
                $position = 5;
            } else {
                $position = 100;
            }
        }

        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        $category = $collection
            ->addAttributeToFilter('parent_id', $category_parent->getId())
            ->addAttributeToFilter('name', $name)
            ->getFirstItem();

        if (is_null($category->getId())) {
            $category
                ->setParentId($category_parent->getId())
                ->setUrlKey($url_key . '/' . strtolower($name))
                ->setName($name)
                ->setIsActive(true);

            if (!is_null($position)) {
                $category->setPosition($position);
            }

            $category = $this->categoryRepository->save($category);
        }

        return $category;
    }

    /**
     * @param $code
     * @param $type
     * @return SouthbayProductGroupInterface|null
     */
    private function findProductGroupByCode($code, $type)
    {
        $collection = $this->productGroupCollectionFactory->create();
        $collection->addFieldToFilter(SouthbayProductGroupInterface::ENTITY_CODE, $code);
        $collection->addFieldToFilter(SouthbayProductGroupInterface::ENTITY_TYPE, $type);

        $field = $collection->getFirstItem();

        if (is_null($field->getId())) {
            return null;
        }

        return $field;
    }
}
