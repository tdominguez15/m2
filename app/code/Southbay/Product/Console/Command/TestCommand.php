<?php

namespace Southbay\Product\Console\Command;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Southbay\Product\Model\Import\ProductImporter;
use Southbay\Product\Model\Import\ProductImportFromExcel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\State;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Attribute;
use Magento\Catalog\Model\Product\Gallery\Entry;
use Southbay\Product\Helper\Data as SouthbayHelper;

class TestCommand extends Command
{
    private $appState = null;

    protected function configure()
    {
        $this->setName('southbay:import:product')
            ->setDescription('Test console command')
            ->addArgument('file', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Ruta al archivo')
            ->addArgument('website_id', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Website id')
            ->addArgument('store_id', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Store id')
            ->addArgument('country', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Country Code')
            ->addArgument('category_root', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Category name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $website_id = $input->getArgument('website_id');
        $store_id = $input->getArgument('store_id');
        $country = $input->getArgument('country');
        $category_root = $input->getArgument('category_root');

        $output->writeln('Init import...');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var ProductImportFromExcel $interface
         */
        $interface = $objectManager->get(ProductImportFromExcel::class);
        $interface->import($file, $store_id, $website_id, $country, $category_root);

        $output->writeln('End import');

        return 1;
    }

    protected function executetestvarios(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var AttributeRepositoryInterface $interface
         */
        $interface = $objectManager->get(AttributeRepositoryInterface::class);
        $attr = $interface->get(Product::ENTITY, 'southbay_variant');

        $output->writeln('attr: ' . $attr->getAttributeId());

        /**
         * @var \Magento\Catalog\Model\ProductRepository $prepository
         */
        $prepository = $objectManager->create('Magento\Catalog\Model\ProductRepository');
        $product = $prepository->get('FN4446-001', false, 4);
        $l = $product->getData('southbay_variant');

        $output->writeln('=== ' . $l);

        return 1;
    }


    protected function executefff(InputInterface $input, OutputInterface $output)
    {
        $file = __DIR__ . '/csvjson.json';
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        $samples = [];
        $total = 0;

        foreach ($data as $item) {
            $total++;
            $sku = $item['Material Proveedor'];
            if (!isset($samples[$sku])) {
                $samples[$sku] = [];
            }
            $samples[$sku][] = $item;
        }

        $output->writeln('total: ' . $total);
        $output->writeln('total samples:' . count($samples));

        /*
        $list = [];
        $total = 0;
        $total_parts = 0;

        foreach ($samples as $items) {
            $total++;
            if (empty($list)) {
                $list = $items;
            } else {
                $list = array_merge($list, $items);
            }

            if ($total == 100) {
                $total_parts++;
                file_put_contents(__DIR__ . '/part-' . $total_parts . '.json', json_encode($list));

                $total = 0;
                $list = [];
            }
        }

        if (!empty($list)) {
            $total_parts++;
            file_put_contents(__DIR__ . '/part-' . $total_parts . '.json', json_encode($list));
        }
        */

        return 1;
    }


    protected function executeimpoooor(InputInterface $input, OutputInterface $output)
    {
        $p = new ProductImporter();
        // $p->runSetup();

        $file = __DIR__ . '/faltantes.json';
        // $file = $input->getArgument('file');
        // $file = __DIR__ . '/' . $file;
        $output->writeln($file);
        $p->importProducts($output, $file);

        /*
        $a = $p->findAttributeByCode('southbay_department');
        $options =  $a->getOptions();

        foreach($options as $option) {
            $output->writeln('option: label: ' . $option->getLabel() . '. key: ' . $option->getValue() . '. id: ' . $option->getId());
            $output->writeln(json_encode($option->getData()));
        }

        $a = $p->findAttributeByCode('southbay_size');
        $options =  $a->getOptions();

        foreach($options as $option) {
            $output->writeln('option: label: ' . $option->getLabel() . '. key: ' . $option->getValue() . '. id: ' . $option->getId());
            $output->writeln(json_encode($option->getData()));
        }
        */

        return 1;
    }

    protected function executeTestCreateAttr(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $interface = $objectManager->get(AttributeRepositoryInterface::class);
        $output->writeln(get_class($interface));

        $attr_name = 'southbay_size_v8';
        // $attr_name = 'test2022';

        try {
            $attribute = $interface->get(Product::ENTITY, $attr_name);
        } catch (NoSuchEntityException $e) {
            $attribute = null;
        }

        if (is_null($attribute)) {
            $output->writeln('No existe');

            $attributeFactory = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class);

            $attributeData = [
                'attribute_code' => $attr_name,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_global' => 1,
                'frontend_label' => 'Fabric Color',
                'frontend_input' => 'select',
                'is_user_defined' => 1,
                'is_unique' => 0,
                'is_required' => 1,
                'is_used_for_promo_rules' => 1,
                'is_used_in_grid' => 1,
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                'is_searchable' => 1,
                'is_comparable' => 1,
                'is_visible_in_advanced_search' => 1,
                'is_visible_in_grid' => 1,
                'is_filterable_in_grid' => 1,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 1,
                'is_visible_on_front' => 1,
                'used_in_product_listing' => 1,
                'used_for_sort_by' => 1,
                'is_filterable' => 1,
                'is_filterable_in_search' => 1,
                // 'apply_to' => 'simple,virtual,configurable',
                'backend_type' => 'varchar',
                'option' => [
                    'value' => [
                        'a' => ['A'],
                        'b' => ['B'],
                        'c' => ['C'],
                    ]
                ]
            ];

            $model = $attributeFactory->create();
            $model->addData($attributeData);
            $entityTypeID = $objectManager->create('Magento\Eav\Model\Entity')
                ->setType('catalog_product')
                ->getTypeId();
            $model->setEntityTypeId($entityTypeID);
            $model->save();

            $output->writeln('Attr creado');
        } else {
            $output->writeln('"attribute_code" ' . json_encode($attribute->getData("attribute_code")));
            $output->writeln('"attribute_model" ' . json_encode($attribute->getData("attribute_model")));
            $output->writeln('"backend_model" ' . json_encode($attribute->getData("backend_model")));
            $output->writeln('"backend_type" ' . json_encode($attribute->getData("backend_type")));
            $output->writeln('"backend_table" ' . json_encode($attribute->getData("backend_table")));
            $output->writeln('"frontend_model" ' . json_encode($attribute->getData("frontend_model")));
            $output->writeln('"frontend_input" ' . json_encode($attribute->getData("frontend_input")));
            $output->writeln('"frontend_label" ' . json_encode($attribute->getData("frontend_label")));
            $output->writeln('"frontend_class" ' . json_encode($attribute->getData("frontend_class")));
            $output->writeln('"source_model" ' . json_encode($attribute->getData("source_model")));
            $output->writeln('"is_required" ' . json_encode($attribute->getData("is_required")));
            $output->writeln('"is_user_defined" ' . json_encode($attribute->getData("is_user_defined")));
            $output->writeln('"default_value" ' . json_encode($attribute->getData("default_value")));
            $output->writeln('"is_unique" ' . json_encode($attribute->getData("is_unique")));
            $output->writeln('"note" ' . json_encode($attribute->getData("note")));
            $output->writeln('"attribute_id" ' . json_encode($attribute->getData("attribute_id")));
            $output->writeln('"frontend_input_renderer" ' . json_encode($attribute->getData("frontend_input_renderer")));
            $output->writeln('"is_global" ' . json_encode($attribute->getData("is_global")));
            $output->writeln('"is_visible" ' . json_encode($attribute->getData("is_visible")));
            $output->writeln('"is_searchable" ' . json_encode($attribute->getData("is_searchable")));
            $output->writeln('"is_filterable" ' . json_encode($attribute->getData("is_filterable")));
            $output->writeln('"is_comparable" ' . json_encode($attribute->getData("is_comparable")));
            $output->writeln('"is_visible_on_front" ' . json_encode($attribute->getData("is_visible_on_front")));
            $output->writeln('"is_html_allowed_on_front" ' . json_encode($attribute->getData("is_html_allowed_on_front")));
            $output->writeln('"is_used_for_price_rules" ' . json_encode($attribute->getData("is_used_for_price_rules")));
            $output->writeln('"is_filterable_in_search" ' . json_encode($attribute->getData("is_filterable_in_search")));
            $output->writeln('"used_in_product_listing" ' . json_encode($attribute->getData("used_in_product_listing")));
            $output->writeln('"used_for_sort_by" ' . json_encode($attribute->getData("used_for_sort_by")));
            $output->writeln('"apply_to" ' . json_encode($attribute->getData("apply_to")));
            $output->writeln('"is_visible_in_advanced_search" ' . json_encode($attribute->getData("is_visible_in_advanced_search")));
            $output->writeln('"position" ' . json_encode($attribute->getData("position")));
            $output->writeln('"is_wysiwyg_enabled" ' . json_encode($attribute->getData("is_wysiwyg_enabled")));
            $output->writeln('"is_used_for_promo_rules" ' . json_encode($attribute->getData("is_used_for_promo_rules")));
            $output->writeln('"is_required_in_admin_store" ' . json_encode($attribute->getData("is_required_in_admin_store")));
            $output->writeln('"is_used_in_grid" ' . json_encode($attribute->getData("is_used_in_grid")));
            $output->writeln('"is_visible_in_grid" ' . json_encode($attribute->getData("is_visible_in_grid")));
            $output->writeln('"is_filterable_in_grid" ' . json_encode($attribute->getData("is_filterable_in_grid")));
            $output->writeln('"search_weight" ' . json_encode($attribute->getData("search_weight")));
            $output->writeln('"is_pagebuilder_enabled" ' . json_encode($attribute->getData("is_pagebuilder_enabled")));

            $a = $this->getAttribute($attr_name);
            $options = $a->getOptions();
            $output->writeln('optiones');
            foreach ($options as $op) {
                $output->writeln(json_encode($op->getData()));
            }


        }

        return 1;
    }

    protected function executeAll(InputInterface $input, OutputInterface $output)
    {
        if (is_null($this->appState)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->appState = $objectManager->get(State::class);
            $this->appState->setAreaCode('adminhtml');
        }

        $file = __DIR__ . '/products-2023-01-05.json';
        $content = file_get_contents($file);
        $list = json_decode($content, true);
        $output->writeln('1- Total imports to import: ' . count($list));

        $category_root = $this->createRootCategory($output);

        $departments = $this->departmentCodes();
        $departmentsText = $this->departmentCodesText();
        $genders = $this->genderCodes();
        $gendersText = $this->genderCodesText();
        $ages = $this->ageCodes();
        $agesText = $this->ageCodesText();
        $firstSiluetaList = $this->firstSiluetaCodes();
        $firstSiluetaTextList = $this->firstSiluetaCodesText();
        $sports = $this->sportCodes();
        $sportsText = $this->sportCodesText();
        $secondSiluetaList = $this->secondSilueta();
        $secondSiluetaTextList = $this->secondSiluetaText();

        $categories = [];
        $categories_map = [];
        $magento_categories_map = [];
        $_list = [];

        foreach ($list as $item) {
            if (isset($item['Grupo artíc. ext.'])) {
                $group = $item['Grupo artíc. ext.'];
                if (strlen($group) == 12) {
                    $department = substr($group, 0, 1);
                    $gender = substr($group, 1, 2);
                    $age = substr($group, 3, 2);
                    $firstSilueta = substr($group, 5, 2);
                    $sport = substr($group, 7, 2);
                    $secondSilueta = substr($group, 7, 2);

                    if (in_array($department, $departments)) {
                        if (in_array($gender, $genders)) {
                            if (in_array($age, $ages)) {
                                if (in_array($firstSilueta, $firstSiluetaList)) {
                                    if (in_array($sport, $sports)) {
                                        if (in_array($secondSilueta, $secondSiluetaList)) {
                                            $key_department = 'department_' . $department;

                                            if (!isset($categories[$key_department])) {
                                                $categories[$key_department] = [
                                                    'name' => $departmentsText[$department],
                                                    'type' => 'department',
                                                    'children' => [],
                                                    'parent' => null
                                                ];
                                            }

                                            $category_department = &$categories[$key_department];
                                            $key_gender = 'gender_' . $gender;

                                            if (!isset($categories[$key_gender])) {
                                                $categories[$key_gender] = [
                                                    'name' => $gendersText[$gender],
                                                    'type' => 'gender',
                                                    'children' => [],
                                                    'parent' => $key_department
                                                ];
                                                $category_department['children'][] = $key_gender;
                                            } else if (!in_array($key_gender, $category_department['children'])) {
                                                $category_department['children'][] = $key_gender;
                                            }

                                            $category_gender = &$categories[$key_gender];
                                            $key_age = 'age_' . $age;

                                            if (!isset($categories[$key_age])) {
                                                $categories[$key_age] = [
                                                    'name' => $agesText[$age],
                                                    'type' => 'age',
                                                    'children' => [],
                                                    'parent' => $key_gender
                                                ];
                                                $category_gender['children'][] = $key_age;
                                            } else if (!in_array($key_age, $category_gender['children'])) {
                                                $category_gender['children'][] = $key_age;
                                            }

                                            $category_age = &$categories[$key_age];
                                            $key_firstSilueta = 'first_silueta_' . $firstSilueta;

                                            if (!isset($categories[$key_firstSilueta])) {
                                                $categories[$key_firstSilueta] = [
                                                    'name' => $firstSiluetaTextList[$firstSilueta],
                                                    'type' => 'first_silueta',
                                                    'children' => [],
                                                    'parent' => $key_age
                                                ];
                                                $category_age['children'][] = $key_firstSilueta;
                                            } else if (!in_array($key_firstSilueta, $category_age['children'])) {
                                                $category_age['children'][] = $key_firstSilueta;
                                            }

                                            $category_first_silueta = &$categories[$key_firstSilueta];
                                            $key_sport = 'sport_' . $sport;

                                            if (!isset($categories[$key_sport])) {
                                                $categories[$key_sport] = [
                                                    'name' => $sportsText[$sport],
                                                    'type' => 'sport',
                                                    'children' => [],
                                                    'parent' => $key_firstSilueta
                                                ];
                                                $category_first_silueta['children'][] = $key_sport;
                                            } else if (!in_array($key_sport, $category_first_silueta['children'])) {
                                                $category_first_silueta['children'][] = $key_sport;
                                            }

                                            $category_sport = &$categories[$key_sport];
                                            $key_sencod_silueta = 'second_silueta_' . $secondSilueta;

                                            if (!isset($categories[$key_sencod_silueta])) {
                                                $categories[$key_sencod_silueta] = [
                                                    'name' => $secondSiluetaTextList[$secondSilueta],
                                                    'type' => 'second_silueta',
                                                    'children' => [],
                                                    'parent' => $key_sport
                                                ];
                                                $category_sport['children'][] = $key_sencod_silueta;
                                            } else if (!in_array($key_sencod_silueta, $category_sport['children'])) {
                                                $category_sport['children'][] = $key_sencod_silueta;
                                            }

                                            $item['category_tree'] = $key_department . '/' . $key_gender . '/' . $key_age . '/' . $key_firstSilueta . '/' . $key_sport . '/' . $key_sencod_silueta;

                                            if (!isset($categories_map[$item['category_tree']])) {
                                                $category_tree = explode('/', $item['category_tree']);
                                                $node = '';
                                                $prev_node = '';
                                                $ids = [];

                                                foreach ($category_tree as $str) {
                                                    if (empty($node)) {
                                                        $node = $str;
                                                    } else {
                                                        $prev_node = $node;
                                                        $node .= '/' . $str;
                                                    }
                                                    if (isset($magento_categories_map[$node])) {
                                                        $ids[] = $magento_categories_map[$node];
                                                    } else {
                                                        $id = $this->createCategoryTree($node, $category_root, $categories, $output, (empty($prev_node) ? null : $magento_categories_map[$prev_node]));

                                                        $magento_categories_map[$node] = $id;
                                                        $ids[] = $id;
                                                    }
                                                }

                                                $categories_map[$item['category_tree']] = $ids;
                                                $categories_map[] = $item['category_tree'];
                                            }

                                            $item['category_tree_ids'] = $categories_map[$item['category_tree']];
                                            $item['southbay_department'] = $departmentsText[$department];
                                            $item['southbay_gender'] = $gendersText[$gender];
                                            $item['southbay_age'] = $agesText[$age];
                                            $item['southbay_sport'] = $sportsText[$sport];
                                            $item['southbay_silueta_1'] = $firstSiluetaTextList[$firstSilueta];
                                            $item['southbay_silueta_2'] = $secondSiluetaTextList[$secondSilueta];
                                            $item['southbay_size'] = $item['Tam.1'];
                                            $item['southbay_color'] = $item['COLOR'];
                                            $item['southbay_season_code'] = '2025';
                                            $item['southbay_channel_level_list'] = $item['Segmentación'];
                                            $_list[] = $item;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        $output->writeln('Categorias: ' . json_encode($categories));
        $output->writeln('===============================================');
        $output->writeln('Categorias Map: ' . json_encode($categories_map));

        $list = $_list;

        $output->writeln('2- Total imports to import: ' . count($list));

        foreach ($list as $item) {
            $this->importProduct($item, $output);
        }

        return 1;
    }

    private function getAttribetSet($objectManager, $attributeSetName)
    {
        $attributeSetRepository = $objectManager->get(AttributeSetRepositoryInterface::class);
        $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $filterBuilder = $objectManager->get(FilterBuilder::class);

        $filters[] = $filterBuilder
            ->setField('attribute_set_name')
            ->setConditionType('eq')
            ->setValue($attributeSetName)
            ->create();

        $searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResults = $attributeSetRepository->getList($searchCriteria);
        $attributeSetItems = $searchResults->getItems();

        if (count($attributeSetItems) == 0) {
            return null;
        }

        $key = array_key_first($attributeSetItems);
        return $attributeSetItems[$key];
    }

    private function createCategoryTree($tree_node, $category_root, $map, $output, $magento_category_parent_id = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->create('Magento\Catalog\Model\Category');

        $nodes = explode('/', $tree_node);
        $node = $nodes[count($nodes) - 1];
        $category_to_insert = $map[$node];

        if (is_null($magento_category_parent_id)) {
            $parent = $category_root;
        } else {
            $parent = $objectManager->create('Magento\Catalog\Model\Category')->load($magento_category_parent_id);
        }

        $_category = $category->getCollection()
            ->addAttributeToFilter('parent_id', $parent->getId())
            ->addAttributeToFilter('name', $category_to_insert['name'])
            ->getFirstItem();

        $output->writeln("Resultado busqueda categoria: " . $category_to_insert['name']);

        if (is_null($_category->getId())) {

            $output->writeln('Parent path: ' . $parent->getPath() . '-' . $parent->getUrlKey());

            $category
                ->setUrlKey($parent->getUrlKey() . '-' . $category_to_insert['name'])
                ->setParentId($parent->getId())
                ->setName($category_to_insert['name'])
                ->setIsActive(true);
            $category->save();

            $output->writeln('Subcategoria creada... ' . $category->getId() . '. Node: ' . $tree_node . ' Name: ' . $category->getName() . ' Path: ' . $category->getPath());

            $category->setPath($parent->getPath() . $category->getPath());
            $category->save();

            return $category->getId();
        } else {
            $output->writeln('Subcategoria creada anteriormente... ' . $_category->getId() . '. Node: ' . $tree_node);

            return $_category->getId();
        }
    }

    private function createRootCategory($output)
    {
        $category_root_name = 'Temporada2025v8';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $category = $objectManager->create('Magento\Catalog\Model\Category');
        $_category = $category->getCollection()->addAttributeToFilter('name', $category_root_name)->getFirstItem();

        if (is_null($_category->getId())) {
            $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $parentCategory = $objectManager->create('Magento\Catalog\Model\Category')->load($parentId);

            $category->setPath($parentCategory->getPath())
                ->setParentId($parentId)
                ->setName($category_root_name)
                ->setIsActive(true);
            $category->save();

            $output->writeln('Categoria creada... ' . $category->getId() . ' ' . $category->getName());

            return $category;
        } else {
            $output->writeln('La categoria padre ya existe... ' . $_category->getId() . ' ' . $_category->getName() . ' ' . $_category->getPath());
            /*
            $objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
            */

            $categoryFactory = $objectManager->get(CategoryFactory::class);
            return $categoryFactory->create()->load($_category->getId());

            /*
            $category->delete();
            */
        }
    }

    private function importProduct($item, $output)
    {
        $attributeSetName = 'southbay_attrs';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $attributeSet = $this->getAttribetSet($objectManager, $attributeSetName);

        if (is_null($attributeSet)) {
            $output->writeln('No se encontro el attributes sets ' . $attributeSetName);
            return;
        }

        $attributeSetId = $attributeSet->getAttributeSetId();

        $output->writeln('Attribute set to use: ' . $attributeSetId);
        $output->writeln('Intendo importar producto: ' . $item['Material Proveedor']);

        $configurableproduct = $objectManager->create('Magento\Catalog\Model\Product');
        $_configurableproduct = $configurableproduct->loadByAttribute('sku', $item['Material Proveedor']);

        $output->writeln('$_configurableproduct ' . ($_configurableproduct !== false ? $_configurableproduct->getId() : ' nada'));

        // $this->appState->setAreaCode('frontend');

        if (empty($_configurableproduct) || is_null($_configurableproduct->getId())) {
            $configurableproduct->setData('url_key', urlencode($item['Material Proveedor']));
            $configurableproduct->setSku($item['Material Proveedor']);
            $configurableproduct->setAttributeSetId($attributeSetId);
            $configurableproduct->setTypeId('configurable'); // type of product (simple/virtual/downloadable/configurable)

            $attribute = $objectManager->get(AttributeRepositoryInterface::class)->get(Product::ENTITY, 'southbay_size');

            /*
            $configurableAttributesData = [];
            $configurableAttributesData[] = [
                'id' => null,
                'label' => $attribute->getStoreLabel(),
                'attribute_id' => $attribute->getId(),
                'attribute_code' => $attribute->getAttributeCode(),
                'frontend_label' => $attribute->getStoreLabel(),
                'html_id' => 'configurable_' . $attribute->getId(),
                'backend_type' => $attribute->getBackendType(),
                'type' => $attribute->getFrontendInput(),
                'frontend_input' => $attribute->getFrontendInput(),
                'class' => $attribute->getFrontendClass(),
                'source' => $attribute->getSourceModel(),
                'position' => $attribute->getSortOrder(),
                'options' => [],
            ];
            */

            // $configurableproduct->setAffectConfigurableProductAttributes($attribute->getId());
            // $configurableproduct->setCanSaveConfigurableAttributes(true);
            // $configurableproduct->setConfigurableAttributesData($configurableAttributesData);

            // $configurableproductsdata = array();
            // $configurableproduct->setConfigurableProductsData($configurableproductsdata);
        } else {
            $output->writeln('El producto ya existe...');
            $configurableproduct = $_configurableproduct;
        }

        $configurableproduct->setName($item['NOMBRE']);
        $configurableproduct->setStatus(1); // status enabled/disabled 1/0
        $configurableproduct->setVisibility(4);  // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
        $configurableproduct->setPrice($item['Precio WH']);
        $configurableproduct->setDescription($item['Descripción']);
        $configurableproduct->setTaxClassId(0); // Tax class ID
        $configurableproduct->setWebsiteIds(array(1)); // set website Id
        $configurableproduct->setCategoryIds($item['category_tree_ids']);
        $configurableproduct->setStockData(array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 0,
                'is_in_stock' => 0,
            )
        );

        $configurableproduct->setSouthbayDepartment($item['southbay_department']);
        $configurableproduct->setSouthbayGender($item['southbay_gender']);
        $configurableproduct->setSouthbayAge($item['southbay_age']);
        $configurableproduct->setSouthbaySport($item['southbay_sport']);
        $configurableproduct->setSouthbaySilueta1($item['southbay_silueta_1']);
        $configurableproduct->setSouthbaySilueta2($item['southbay_silueta_2']);
        $configurableproduct->setSouthbaySize($item['southbay_size']);
        $configurableproduct->setSouthbayColor($item['southbay_color']);
        $configurableproduct->setSouthbaySeasonCode($item['southbay_season_code']);
        $configurableproduct->setSouthbayChannelLevelList($item['southbay_channel_level_list']);

        $configurableproduct->save();

        $product_id = $configurableproduct->getId();
        $output->writeln('Producto generado: ' . $product_id);


    }

    protected function executeCheckIfAttrExists(InputInterface $input, OutputInterface $output)
    {
        $eavConfig = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Eav\Model\Config::class);
        $attr = $eavConfig->getAttribute(Customer::ENTITY, 'southbay_canal');

        if (!is_null($attr) && $attr->getId()) {
            $output->writeln("Existe attr");
        } else {
            $output->writeln("No existe attr");
        }

        return 1;
    }

    protected function executeAddOptions(InputInterface $input, OutputInterface $output)
    {
        $attribute = $this->getAttribute('talles');

        if (!is_null($attribute)) {
            $output->writeln("Existe ");
            $options = $attribute->getOptions();
            $output->writeln("options: " . count($options));

            foreach ($options as $option) {
                $output->writeln(get_class($option));
                $output->writeln($option->getLabel());
            }

            /*
            $option = new Option();
            $option->setAttributeId($attribute->getAttributeId());
            $option->setLabel("13");

            $options[] = $option;
            */

            // $manager = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Eav\Model\Entity\Attribute::class)->get;

            $factory = $this->getFactory();

            /**
             * @var \Magento\Eav\Api\Data\AttributeOptionInterface
             */
            $option = $factory->create();
            $option->setLabel("13");

            $manager = $this->getAttrOptionsManagement();
            $manager->add($attribute->getEntityTypeId(), $attribute->getAttributeCode(), $option);

            /*
            if ($this->optionExists($attribute, '11')) {
                $output->writeln("Existe la opcion");
            } else {
                $output->writeln("NO existe la opcion");
            }
            */
        } else {
            $output->writeln("No existe");
        }

        return 1;
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    private function getFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Eav\Api\Data\AttributeOptionInterfaceFactory::class);
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\OptionManagement
     */
    private function getAttrOptionsManagement()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Eav\Model\Entity\Attribute\OptionManagement::class);
    }

    /**
     * @param $code
     * @return Attribute
     */
    private function getAttribute($code)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create(Product::class)->getResource()->getAttribute($code);
    }

    private function optionExists(Option $attribute, $value)
    {
        return $attribute->getOptionIdByValue($value) !== false;
    }

    private function departmentCodes()
    {
        return [
            'V', 'W', 'X', 'Y', 'Z'
        ];
    }

    private function departmentCodesText()
    {
        return [
            'V' => 'CALZADO',
            'W' => 'ROPA',
            'X' => 'ACCESORIOS',
            'Y' => 'TECNOLOGIA',
            'Z' => 'MISCELANEO'
        ];
    }

    private function genderCodes()
    {
        return [
            '01', '02', '03'
        ];
    }

    private function genderCodesText()
    {
        return [
            '01' => 'MASCULINO', '02' => 'FEMENINO', '03' => 'UNISEX'
        ];
    }

    private function ageCodes()
    {
        return [
            '01', '02', '03', '04', '05'
        ];
    }

    private function ageCodesText()
    {
        return [
            '01' => 'ADULTO', '02' => 'JOVEN', '03' => 'PREESCOLAR', '04' => 'INFANTE', '05' => 'GENERICO'
        ];
    }

    private function firstSiluetaCodes()
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52'
        ];
    }

    private function firstSiluetaCodesText()
    {
        return [
            '01' => 'ABRIGO', '02' => 'BALONES', '03' => 'BASCULA', '04' => 'BOCINAS', '05' => 'BOLSOS', '06' => 'BOTAS', '07' => 'BRA', '08' => 'CAMISETA O REMERA', '09' => 'CHALECO', '10' => 'COLCHONETA', '11' => 'CONJUNTO', '12' => 'CONSUMO', '13' => 'EJERCITADOR', '14' => 'ENTERIZO', '15' => 'FALDA', '16' => 'GORRAS', '17' => 'GORROS', '18' => 'GUANTES', '19' => 'LENTES', '20' => 'LIMPIEZA', '21' => 'MEDIAS', '22' => 'MOCHILAS', '23' => 'PANTALON', '24' => 'PELOTA', '25' => 'PESAS', '26' => 'POLO', '27' => 'PROTECTORES', '28' => 'RELOJES', '29' => 'REPLICAS O JERSEYS', '30' => 'SANDALIAS', '31' => 'TACOS', '32' => 'TACOS SUELA DE GOMA', '33' => 'TAQUILLOS', '34' => 'TERMOS', '35' => 'TRAJE DE BAÑO', '36' => 'VESTIDO', '37' => 'ZAPATILLAS', '38' => 'ZAPATOS', '39' => 'MALETA', '40' => 'SAUNA', '41' => 'HORMADOR', '42' => 'TOALLAS', '43' => 'CORREA', '44' => 'AUDIFONOS', '45' => 'PLANTILLAS', '46' => 'BODIES', '47' => 'INTERIORES', '48' => 'OTROS', '49' => 'FLOTADOR', '50' => 'CHAPALETA', '51' => 'FUNDA', '52' => 'SERVICIOS'
        ];
    }

    private function sportCodes()
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24'
        ];
    }

    private function sportCodesText()
    {
        return [
            '01' => 'BALONCESTO', '02' => 'BEISBOL', '03' => 'BLUETOOTH', '04' => 'BOXEO', '05' => 'CASUAL', '06' => 'CICLISMO', '07' => 'CORRER', '08' => 'ENTRENAMIENTO', '09' => 'ESCOLARES', '10' => 'FUT AMERICANO', '11' => 'FUTBOL', '12' => 'GOLF', '13' => 'NATACION', '14' => 'PADEL', '15' => 'RUGBY', '16' => 'SKATE', '17' => 'TENIS', '18' => 'TRAIL', '19' => 'UNIFORME', '20' => 'VOLEIBOL', '21' => 'YOGA', '22' => 'INTELIGENTE', '23' => 'ATLETISMO', '24' => 'BAILE'
        ];
    }

    private function secondSilueta()
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93'
        ];
    }

    private function secondSiluetaText()
    {
        return [
            '01' => '3/4 ALTAS', '02' => 'ALTAS', '03' => 'BAJAS', '04' => 'CON CAPUCHA', '05' => 'SIN CAPUCHA', '06' => 'ZIPPER 1/4', '07' => 'ACOLCHADO', '08' => 'NO ACOLCHADO', '09' => 'SOPORTE ALTO', '10' => 'SOPORTE BAJO', '11' => 'SOPORTE MEDIO', '12' => 'CRUZADO', '13' => 'MANGA CORTA', '14' => 'MANGA LARGA', '15' => 'SIN MANGAS', '16' => 'DOS PIEZAS', '17' => 'UNA PIEZA', '18' => 'CORTO', '19' => 'LARGO', '20' => 'DOS EN UNO', '21' => '5" CORTO', '22' => '6" CORTO', '23' => '7" CORTO', '24' => 'CORTO A LA RODILLA', '25' => 'LARGO AJUSTADO', '26' => 'TRES CUARTOS (3/4)', '27' => 'TRES CUARTOS (3/4) AJUSTADO', '28' => 'DOS EN UNO', '29' => 'BIKER', '30' => 'CORTO DOS EN UNO', '31' => 'CORTO AJUSTADO', '32' => 'MINI', '33' => 'ELIPTICO', '34' => 'REDONDO', '35' => 'TAQUERA', '36' => 'CANGURERA', '37' => 'CRUZADO', '38' => 'CARTERA', '39' => 'MENSAJERO', '40' => 'BATERA', '41' => 'NECESER', '42' => 'ESTERILLA', '43' => 'BARRA ENERGETICA', '44' => 'BEBIDA ENERGETICA', '45' => 'CUERDA DE SALTAR', '46' => 'LIGAS RESISTENCIA', '47' => 'CINTURON', '48' => 'BASE PARA FLEXIONES', '49' => 'VENDAS BOXEO', '50' => 'EMPUÑADURA', '51' => 'BANDAS DE RESISTENCIA', '52' => 'AJUSTABLES', '53' => 'CERRADAS', '54' => 'VISERA', '55' => 'PESCADOR', '56' => 'BEANIE', '57' => 'SOL', '58' => 'KIT', '59' => 'CAPA DURA', '60' => 'CAPA FLEXIBLE', '61' => 'INVISIBLES', '62' => 'TOBILLERA', '63' => 'LARGAS', '64' => 'ALTAS A LA RODILLA', '65' => 'SACO DE GIMNASIA', '66' => 'MANCUERNA', '67' => 'RUSAS', '68' => 'AJUSTABLE MUÑECA', '69' => 'AJUSTABLE TOBILLO', '70' => 'NARICERA', '71' => 'TAPONES DE OIDO', '72' => 'RODILLERAS', '73' => 'MENISQUERA', '74' => 'MUÑEQUERA', '75' => 'CODERAS', '76' => 'BUCALES', '77' => 'CASCOS', '78' => 'BARBILLA', '79' => 'ESPINILLERA', '80' => 'FAJA', '81' => 'PLASTICO', '82' => 'METAL', '83' => 'RODILLOS', '84' => 'AUTOMATICA', '85' => 'SILICON', '86' => 'PROTECCIÓN', '87' => 'CLASICAS', '88' => 'DEPORTIVAS', '89' => 'INALAMBRICO', '90' => 'CON CABLE', '91' => 'MALETIN', '92' => 'MANGA', '93' => 'HEADBANDS'
        ];
    }


    protected function executeSizeSort(InputInterface $input, OutputInterface $output)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Magento\Catalog\Model\ProductRepository $prepository
         */
        $prepository = $objectManager->create('Magento\Catalog\Model\ProductRepository');
        $product = $prepository->get('FV5285-003');
        /**
         * @var SouthbayHelper $helper
         */
        $helper = $objectManager->get(SouthbayHelper::class);
        $sizes = $helper->getChildrenLabels($product);

        foreach ($sizes as $s) {
            $output->writeln('s: ' . $s['label']);
        }

        [$startColumn, $startRow] = Coordinate::coordinateFromString('A1');
        $startColumn++;

        $output->writeln(json_encode($startColumn));
        $output->writeln(json_encode($startRow));


        return 1;
    }

    protected function executeTest(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $appState = $objectManager->get(State::class);
        $appState->setAreaCode('adminhtml');

        /**
         * @var \Magento\Catalog\Model\ProductRepository $prepository
         */
        $prepository = $objectManager->create('Magento\Catalog\Model\ProductRepository');

        $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName('AURORA_DZ7533-004_PHCFH001-2000_7.jpeg');
        $dispersionPath = \Magento\MediaStorage\Model\File\Uploader::getDispersionPath($fileName);
        $fileName = $dispersionPath . '/' . $fileName;

        $output->writeln($fileName);

        $configurableproduct = $prepository->getById(947);

        if (is_null($configurableproduct)) {
            $output->writeln('es null');
            return 1;
        }

        $entries = $configurableproduct->getMediaGalleryEntries();

        /**
         * @var \Magento\Catalog\Model\Product\Gallery\Entry $entry
         */
        foreach ($entries as $entry) {
            if ($entry->getData('file') == $fileName) {
                $output->writeln('ya existe');
            }
        }

        return 1;
    }

}
