<?php

namespace Southbay\Product\Model\Import;

use Magento\Catalog\Api\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface as EavAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Api\Data\ImageInterfaceFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImporter
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    private function getObjectManager(): \Magento\Framework\App\ObjectManager
    {
        if (!$this->objectManager) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        }
        return $this->objectManager;
    }

    public function runSetup(): void
    {
        $this->addAttributes();
        $this->createAttributeSet();
        // $this->importSeasonTypes();
    }

    public function importProducts(OutputInterface $output, $file): void
    {
        // $path_images = '/var/www/html/magento/pub/media/fotos';
        // $images = scandir($path_images);
        $images = []; // $this->groupImages();

        $content = file_get_contents($file);
        $list = json_decode($content, true);
        $output->writeln('1- Total imports to import: ' . count($list));

        $category_root = $this->createRootCategory($output, 'Temporada SP2025 (UY)');

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

        $output->writeln('2- paso dos');

        $categories = [];
        $categories_map = [];
        $magento_categories_map = [];
        $_list = [];
        $color_options = [];
        $size_options = [];
        $total = 0;

        $output->writeln('3- paso');

        foreach ($list as $item) {
            $total++;

            if (isset($item['Grupo artíc. ext.'])) {
                $tt1 = substr($item['Grupo artíc. ext.'], 0, 9);
                $tt2 = substr($item['Grupo artíc. ext.'], 9);
                $item['Grupo artíc. ext.'] = $tt1 . '_' . $tt2;
                $item['Grupo artíc. ext.'] = str_replace('__', '_', $item['Grupo artíc. ext.']);

                $group = $item['Grupo artíc. ext.'];

                $output->writeln($group . '-' . strlen($group));

                if (strlen($group) == 12) {
                    $department = substr($group, 0, 1);
                    $gender = substr($group, 1, 2);
                    $age = substr($group, 3, 2);
                    $firstSilueta = substr($group, 5, 2);
                    $sport = substr($group, 7, 2);
                    $secondSilueta = substr($group, 10, 2);

                    $output->writeln('======================================');
                    $output->writeln($department . ':' . in_array($department, $departments));
                    $output->writeln($gender . ':' . in_array($gender, $genders));
                    $output->writeln($age . ':' . in_array($age, $ages));
                    $output->writeln($firstSilueta . ':' . in_array($firstSilueta, $firstSiluetaList));
                    $output->writeln($sport . ':' . in_array($sport, $sports));
                    $output->writeln($secondSilueta . ':' . in_array($secondSilueta, $secondSiluetaList));
                    $output->writeln('======================================');

                    if (in_array($department, $departments)) {
                        if (in_array($gender, $genders)) {
                            if (in_array($age, $ages)) {
                                if (in_array($firstSilueta, $firstSiluetaList)) {
                                    if (in_array($sport, $sports)) {
                                        if (in_array($secondSilueta, $secondSiluetaList)) {
                                            $output->writeln('enn');
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

                                            $item['category_tree'] = $key_department . '/' . $key_gender . '/' . $key_age . '/' . $key_sport . '/' . $key_firstSilueta . '/' . $key_sencod_silueta;

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
                                                        $id = $this->createoryTree($node, $category_root, $categories, $output, (empty($prev_node) ? null : $magento_categories_map[$prev_node]));

                                                        $magento_categories_map[$node] = $id;
                                                        $ids[] = $id;
                                                    }
                                                }

                                                $categories_map[$item['category_tree']] = $ids;
                                                $categories_map[] = $item['category_tree'];
                                            }

                                            $item['category_tree_ids'] = $categories_map[$item['category_tree']];
                                            $item['southbay_department'] = $categories[$key_department]['name'];
                                            $item['southbay_gender'] = $categories[$key_gender]['name'];
                                            $item['southbay_age'] = $categories[$key_age]['name'];
                                            $item['southbay_sport'] = $categories[$key_sport]['name'];
                                            $item['southbay_silueta_1'] = $categories[$key_firstSilueta]['name'];;
                                            $item['southbay_silueta_2'] = $categories[$key_sencod_silueta]['name'];;
                                            $item['southbay_size'] = strval($item['Tam.1']);
                                            $item['southbay_color'] = $item['COLOR'];
                                            $item['southbay_season_code'] = '2025';
                                            $item['southbay_channel_level_list'] = $item['Segmentación'];

                                            if (!empty($item['southbay_channel_level_list'])) {
                                                $item['southbay_channel_level_list'] = ';' . trim($item['southbay_channel_level_list'], ';') . ';';
                                            }

                                            $_list[] = $item;

                                            if (!isset($color_options[$item['southbay_color']])) {
                                                $color_options[$item['southbay_color']] = $item['southbay_color'];
                                            }

                                            if (!isset($size_options[$item['southbay_size']])) {
                                                $size_options[$item['southbay_size']] = $item['southbay_size'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $output->writeln('4- paso');

        $color_options = $this->updateSouthbayColorOptions($color_options);
        $size_options = $this->updateSouthbaySizeOptions($size_options);
        $department_options = $this->transformAttrOptions('southbay_department');
        $gender_options = $this->transformAttrOptions('southbay_gender');
        $age_options = $this->transformAttrOptions('southbay_age');
        $sport_options = $this->transformAttrOptions('southbay_sport');
        $first_silueta_options = $this->transformAttrOptions('southbay_silueta_1');
        $second_silueta_options = $this->transformAttrOptions('southbay_silueta_2');

        $appState = $this->getObjectManager()->get(State::class);
        $appState->setAreaCode('adminhtml');

        $list = $_list;
        $map = [];
        $_all = [];
        $_list = [];

        $output->writeln('5- paso');

        foreach ($list as $item) {
            $item['sku'] = $item['Material Proveedor'];
            $item['sku2'] = $item['concat nike'];
            $item['southbay_size'] = $size_options[$item['southbay_size']]->getValue();
            $item['southbay_color'] = $color_options[$item['southbay_color']]->getValue();
            $item['southbay_department'] = $department_options[$item['southbay_department']]->getValue();
            $item['southbay_gender'] = $gender_options[$item['southbay_gender']]->getValue();
            $item['southbay_age'] = $age_options[$item['southbay_age']]->getValue();
            $item['southbay_sport'] = $sport_options[$item['southbay_sport']]->getValue();
            $item['southbay_silueta_1'] = $first_silueta_options[$item['southbay_silueta_1']]->getValue();
            $item['southbay_silueta_2'] = $second_silueta_options[$item['southbay_silueta_2']]->getValue();

            if (!isset($map[$item['sku']])) {
                $map[$item['sku']] = [
                    'parent' => $item,
                    'children' => []
                ];
                $_list[] = &$map[$item['sku']];
            }

            if (!isset($_all[$item['sku2']])) {
                $_all[$item['sku2']] = true;
                $map[$item['sku']]['children'][] = $item;
            }
        }

        $list = $_list;

        $attributeSetName = $this->getAttributeSetName();
        $attributeSet = $this->findAttributeSetByName($attributeSetName);

        if (is_null($attributeSet)) {
            $output->writeln('No se encontro el attributes sets ' . $attributeSetName);
            return;
        }

        $attributeSetId = $attributeSet->getAttributeSetId();
        $southbay_size_attr = $this->findAttributeByCode('southbay_size');


        $total = 0;
        $size = count($list);

        $output->writeln('6- paso:  list' . count($list));

        foreach ($list as $item) {
            $total++;
            $output->writeln('procesando item: ' . $total . '/' . $size);
            $this->importProduct($item, $attributeSetId, $southbay_size_attr, $output, $images);
        }
    }

    public function imagesPath()
    {
        $objectManager = $this->getObjectManager();
        /**
         * @var \Magento\Framework\Filesystem\DirectoryList $dir
         */
        $dir = $objectManager->get('Magento\Framework\Filesystem\DirectoryList');
        return $dir->getRoot() . '/pub/media/fotos';
    }

    public function groupImages()
    {
        $path_images = $this->imagesPath();
        $images = scandir($path_images);

        $result = [];

        // 553558-043-PV.png
        // AURORA_SX7554-100_PHSBH001-2000.jpeg
        foreach ($images as $image) {
            if (str_starts_with($image, 'AURORA_')) {
                $parts = explode('_', $image);
                if (count($parts) === 3) {
                    $sku = $parts[1];
                    if (!isset($result[$sku])) {
                        $result[$sku] = [];
                    }

                    $result[$sku][] = $path_images . '/' . $image;
                }
            } else {
                $parts = explode('-', $image);
                if (count($parts) === 3) {
                    $sku = $parts[0] . '-' . $parts[1];

                    if (!isset($result[$sku])) {
                        $result[$sku] = [];
                    }

                    $result[$sku][] = $path_images . '/' . $image;
                }
            }
        }

        return $result;
    }

    private function createAttributeSet()
    {
        $attributeSet = $this->findAttributeSetByName($this->getAttributeSetName());

        // Decomentar si se quiere eliminar el attr set si existe previamente
        /*
        if (!is_null($attr_set)) {
            $attr_set->delete();
            $attr_set = null;
        }
        */

        $eavSetup = $this->getObjectManager()->get(\Magento\Eav\Setup\EavSetup::class);

        if (is_null($attributeSet)) {
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
                $attributeSet->getId(), // $attributeSet['attribute_set_id'],
                // $eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSet['attribute_set_id']), $attributeId
                $eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSet->getId()), $attributeId
            );
        }
    }

    private function getAttributeSetName()
    {
        return 'Southbay attr set';
    }

    private function createRootCategory($output, $seasonName)
    {
        $category_root_name = $seasonName;

        $category = $this->getObjectManager()->create('Magento\Catalog\Model\Category');
        $_category = $category->getCollection()->addAttributeToFilter('name', $category_root_name)->getFirstItem();

        if (is_null($_category->getId())) {
            $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $parentCategory = $this->getObjectManager()->create('Magento\Catalog\Model\Category')->load($parentId);

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

            $categoryFactory = $this->getObjectManager()->get(CategoryFactory::class);
            return $categoryFactory->create()->load($_category->getId());

            /*
            $category->delete();
            */
        }
    }

    private function getAttributes()
    {
        return [
            'southbay_department' => ['frontend_label' => 'Departamento', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => $this->transforToOptionValues($this->departmentCodesText(), 'southbay_department')]],
            'southbay_gender' => ['frontend_label' => 'Genero', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => $this->transforToOptionValues($this->genderCodesText(), 'southbay_gender')]],
            'southbay_age' => ['frontend_label' => 'Edad', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => $this->transforToOptionValues($this->ageCodesText(), 'southbay_age')]],
            'southbay_sport' => ['frontend_label' => 'Deporte', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => $this->transforToOptionValues($this->sportCodesText(), 'southbay_sport')]],
            'southbay_size' => ['frontend_label' => 'Talle', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select', 'option' => ['value' => []]],
            'southbay_color' => ['frontend_label' => 'Color', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select', 'option' => ['value' => []]],
            'southbay_season_code' => [
                'frontend_label' => 'Temporada',
                'is_required' => 0,
                'is_used_in_grid' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
            'southbay_silueta_1' => ['frontend_label' => 'Silueta', 'is_required' => 0, 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => $this->transforToOptionValues($this->firstSiluetaCodesText(), 'southbay_silueta_1')]],
            'southbay_silueta_2' => ['frontend_label' => 'Características', 'is_required' => 0, 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => $this->transforToOptionValues($this->secondSiluetaText(), 'southbay_silueta_2')]],
            'southbay_channel_level_list' => [
                'frontend_label' => 'Canal y Nivel',
                'is_used_in_grid' => 0,
                'is_required' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
            'southbay_price_retail' => [
                'frontend_label' => 'Precio Minorista',
                'backend_type' => 'decimal',
                'is_used_in_grid' => 0,
                'is_required' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
            'southbay_purchase_unit' => [
                'backend_type' => 'int',
                'frontend_label' => 'Unidad de Compra',
                'is_used_in_grid' => 0,
                'is_required' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
        ];
    }

    private function getDefaultAttributeConfig(): array
    {
        return [
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_global' => 1,
            'attribute_group_name' => 'Southbay',
            'frontend_input' => 'text',
            'is_user_defined' => 1,
            'is_unique' => 0,
            'is_required' => 1,
            'is_used_for_promo_rules' => 1,
            'is_used_in_grid' => 1,
            'is_searchable' => 0,
            'is_comparable' => 0,
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
            'backend_type' => 'varchar'
        ];
    }

    /**
     * Agrega los atributos de productos
     * @return void
     */
    public function addAttributes(): void
    {
        $attributes = $this->getAttributes();
        $attributeFactory = $this->getObjectManager()->get(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class);

        foreach ($attributes as $code => $config) {
            $_config = $this->getDefaultAttributeConfig();
            if (!empty($config)) {
                $_config = array_merge($_config, $config);
            }

            $attribute = $this->findAttributeByCode($code);

            // Descomentar si se quiere liminar el atributo si existe previamentel
            /*
            if (!is_null($attribute)) {
                $attribute->delete();
                $attribute = null;
            }
            */

            if (is_null($attribute)) {
                $_config['attribute_code'] = $code;

                $this->createProductAttribute($_config, $attributeFactory);
            }
        }
    }

    public function findAttributeByCode($code)
    {
        $interface = $this->getObjectManager()->get(AttributeRepositoryInterface::class);
        try {
            $attribute = $interface->get(Product::ENTITY, $code);
        } catch (NoSuchEntityException $e) {
            $attribute = null;
        }

        return $attribute;
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

    /**
     * Crea un nuevo atributo para los productos
     * Si ya existe no hace nada
     * @return void
     */
    public function createProductAttribute($attributeData, $attributeFactory): void
    {
        $model = $attributeFactory->create();
        $model->addData($attributeData);
        $model->setEntityTypeId($this->getProductEntityTypeId());
        $model->save();
    }

    public function departmentCodes(): array
    {
        return [
            'V', 'W', 'X', 'Y', 'Z'
        ];
    }

    public function departmentCodesText(): array
    {
        return [
            'V' => 'CALZADO',
            'W' => 'ROPA',
            'X' => 'ACCESORIOS',
            'Y' => 'TECNOLOGIA',
            'Z' => 'MISCELANEO'
        ];
    }

    public function genderCodes(): array
    {
        return [
            '01', '02', '03'
        ];
    }

    public function genderCodesText(): array
    {
        return [
            '01' => 'MASCULINO', '02' => 'FEMENINO', '03' => 'UNISEX'
        ];
    }

    public function ageCodes(): array
    {
        return [
            '01', '02', '03', '04', '05'
        ];
    }

    public function ageCodesText(): array
    {
        return [
            '01' => 'ADULTO', '02' => 'JOVEN', '03' => 'PREESCOLAR', '04' => 'INFANTE', '05' => 'GENERICO'
        ];
    }

    public function firstSiluetaCodes(): array
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53'
        ];
    }

    public function firstSiluetaCodesText(): array
    {
        return [
            '01' => 'ABRIGO', '02' => 'BALONES', '03' => 'BASCULA', '04' => 'BOCINAS', '05' => 'BOLSOS', '06' => 'BOTAS', '07' => 'BRA', '08' => 'CAMISETA O REMERA', '09' => 'CHALECO', '10' => 'COLCHONETA', '11' => 'CONJUNTO', '12' => 'CONSUMO', '13' => 'EJERCITADOR', '14' => 'ENTERIZO', '15' => 'FALDA', '16' => 'GORRAS', '17' => 'GORROS', '18' => 'GUANTES', '19' => 'LENTES', '20' => 'LIMPIEZA', '21' => 'MEDIAS', '22' => 'MOCHILAS', '23' => 'PANTALON', '24' => 'PELOTA', '25' => 'PESAS', '26' => 'POLO', '27' => 'PROTECTORES', '28' => 'RELOJES', '29' => 'REPLICAS O JERSEYS', '30' => 'SANDALIAS', '31' => 'TACOS', '32' => 'TACOS SUELA DE GOMA', '33' => 'TAQUILLOS', '34' => 'TERMOS', '35' => 'TRAJE DE BAÑO', '36' => 'VESTIDO', '37' => 'ZAPATILLAS', '38' => 'ZAPATOS', '39' => 'MALETA', '40' => 'SAUNA', '41' => 'HORMADOR', '42' => 'TOALLAS', '43' => 'CORREA', '44' => 'AUDIFONOS', '45' => 'PLANTILLAS', '46' => 'BODIES', '47' => 'INTERIORES', '48' => 'OTROS', '49' => 'FLOTADOR', '50' => 'CHAPALETA', '51' => 'FUNDA', '52' => 'SERVICIOS', '53' => 'BAJAS'
        ];
    }

    public function sportCodes(): array
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '36'
        ];
    }

    public function sportCodesText(): array
    {
        return [
            '01' => 'BALONCESTO', '02' => 'BEISBOL', '03' => 'BLUETOOTH', '04' => 'BOXEO', '05' => 'CASUAL', '06' => 'CICLISMO', '07' => 'CORRER', '08' => 'ENTRENAMIENTO', '09' => 'ESCOLARES', '10' => 'FUT AMERICANO', '11' => 'FUTBOL', '12' => 'GOLF', '13' => 'NATACION', '14' => 'PADEL', '15' => 'RUGBY', '16' => 'SKATE', '17' => 'TENIS', '18' => 'TRAIL', '19' => 'UNIFORME', '20' => 'VOLEIBOL', '21' => 'YOGA', '22' => 'INTELIGENTE', '23' => 'ATLETISMO', '24' => 'BAILE',
            '36' => 'ENTRETENIMIENTO'
        ];
    }

    public function secondSilueta(): array
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93'
        ];
    }

    public function secondSiluetaText(): array
    {
        return [
            '01' => '3/4 ALTAS', '02' => 'ALTAS', '03' => 'BAJAS', '04' => 'CON CAPUCHA', '05' => 'SIN CAPUCHA', '06' => 'ZIPPER 1/4', '07' => 'ACOLCHADO', '08' => 'NO ACOLCHADO', '09' => 'SOPORTE ALTO', '10' => 'SOPORTE BAJO', '11' => 'SOPORTE MEDIO', '12' => 'CRUZADO', '13' => 'MANGA CORTA', '14' => 'MANGA LARGA', '15' => 'SIN MANGAS', '16' => 'DOS PIEZAS', '17' => 'UNA PIEZA', '18' => 'CORTO', '19' => 'LARGO', '20' => 'DOS EN UNO', '21' => '5" CORTO', '22' => '6" CORTO', '23' => '7" CORTO', '24' => 'CORTO A LA RODILLA', '25' => 'LARGO AJUSTADO', '26' => 'TRES CUARTOS (3/4)', '27' => 'TRES CUARTOS (3/4) AJUSTADO', '28' => 'DOS EN UNO', '29' => 'BIKER', '30' => 'CORTO DOS EN UNO', '31' => 'CORTO AJUSTADO', '32' => 'MINI', '33' => 'ELIPTICO', '34' => 'REDONDO', '35' => 'TAQUERA', '36' => 'CANGURERA', '37' => 'CRUZADO', '38' => 'CARTERA', '39' => 'MENSAJERO', '40' => 'BATERA', '41' => 'NECESER', '42' => 'ESTERILLA', '43' => 'BARRA ENERGETICA', '44' => 'BEBIDA ENERGETICA', '45' => 'CUERDA DE SALTAR', '46' => 'LIGAS RESISTENCIA', '47' => 'CINTURON', '48' => 'BASE PARA FLEXIONES', '49' => 'VENDAS BOXEO', '50' => 'EMPUÑADURA', '51' => 'BANDAS DE RESISTENCIA', '52' => 'AJUSTABLES', '53' => 'CERRADAS', '54' => 'VISERA', '55' => 'PESCADOR', '56' => 'BEANIE', '57' => 'SOL', '58' => 'KIT', '59' => 'CAPA DURA', '60' => 'CAPA FLEXIBLE', '61' => 'INVISIBLES', '62' => 'TOBILLERA', '63' => 'LARGAS', '64' => 'ALTAS A LA RODILLA', '65' => 'SACO DE GIMNASIA', '66' => 'MANCUERNA', '67' => 'RUSAS', '68' => 'AJUSTABLE MUÑECA', '69' => 'AJUSTABLE TOBILLO', '70' => 'NARICERA', '71' => 'TAPONES DE OIDO', '72' => 'RODILLERAS', '73' => 'MENISQUERA', '74' => 'MUÑEQUERA', '75' => 'CODERAS', '76' => 'BUCALES', '77' => 'CASCOS', '78' => 'BARBILLA', '79' => 'ESPINILLERA', '80' => 'FAJA', '81' => 'PLASTICO', '82' => 'METAL', '83' => 'RODILLOS', '84' => 'AUTOMATICA', '85' => 'SILICON', '86' => 'PROTECCIÓN', '87' => 'CLASICAS', '88' => 'DEPORTIVAS', '89' => 'INALAMBRICO', '90' => 'CON CABLE', '91' => 'MALETIN', '92' => 'MANGA', '93' => 'HEADBANDS'
        ];
    }

    public function segmentation(): array
    {
        return [
            'NDDC',
            'NSO',
            'NSP',
            'SG',
            'AS',
            'CS BKST',
            'CS RUN',
            'CS NIKE SB',
            'CS FTBL',
            'NBHD',
            'MELI',
            'NVS',
            'FRONTERA'
        ];
    }

    private function seasonTypes(): array
    {
        return [
            ['code' => '001', 'name' => 'Spring'],
            ['code' => '002', 'name' => 'Summer'],
            ['code' => '003', 'name' => 'Fall'],
            ['code' => '004', 'name' => 'Holliday'],
            ['code' => '005', 'name' => 'Sin Temporada'],
            ['code' => '006', 'name' => 'Carry Over'],
            ['code' => '999', 'name' => 'N/A']
        ];
    }

    private function transforToOptionValues($list, $code)
    {
        $result = [];

        foreach ($list as $key => $text) {
            $result[$code . '_' . $key] = [$text];
        }

        return $result;
    }

    private function getProductEntityTypeId()
    {
        return $this->getObjectManager()->create('Magento\Eav\Model\Entity')
            ->setType(Product::ENTITY)
            ->getTypeId();
    }

    private function createoryTree($tree_node, $category_root, $map, $output, $magento_category_parent_id = null)
    {
        $objectManager = $this->getObjectManager();
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

        // $output->writeln("Resultado busqueda categoria: " . $category_to_insert['name']);

        if (is_null($_category->getId())) {

            //  $output->writeln('Parent path: ' . $parent->getPath() . '-' . $parent->getUrlKey());

            $category
                ->setUrlKey($parent->getUrlKey() . '-' . $category_to_insert['name'])
                ->setParentId($parent->getId())
                ->setName($category_to_insert['name'])
                ->setIsActive(true);
            $category->save();

            // $output->writeln('Subcategoria creada... ' . $category->getId() . '. Node: ' . $tree_node . ' Name: ' . $category->getName() . ' Path: ' . $category->getPath());

            $category->setPath($parent->getPath() . $category->getPath());
            $category->save();

            return $category->getId();
        } else {
            // $output->writeln('Subcategoria creada anteriormente... ' . $_category->getId() . '. Node: ' . $tree_node);

            return $_category->getId();
        }
    }

    public function transformAttrOptions($code)
    {
        $result = [];

        $attribute = $this->findAttributeByCode($code);
        $_options = $attribute->getOptions();

        foreach ($_options as $option) {
            $result[$option->getLabel()] = $option;
        }

        return $result;
    }

    private function cleanOptions($code)
    {
        $attribute = $this->findAttributeByCode($code);
        $_options = $attribute->getOptions();

        foreach ($_options as $option) {
            $option->delete();
        }
    }

    private function updateAttrOptions($code, $options)
    {
        // $result = $this->transformAttrOptions($code);
        $result = [];

        /**
         * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
         */
        $attribute = $this->findAttributeByCode($code);
        $source = $attribute->getSource();

        $factory = $this->getObjectManager()->get(\Magento\Eav\Api\Data\AttributeOptionInterfaceFactory::class);
        $manager = $this->getObjectManager()->get(\Magento\Eav\Model\Entity\Attribute\OptionManagement::class);

        foreach ($options as $key => $value) {
            $value = trim(strval($value));
            $key = $code . '_' . trim(strval($key));
            $option_id = $source->getOptionId($value);
            if (is_null($option_id)) {
                $_option = $factory->create();
                $_option->setValue($key);
                $_option->setLabel($value);

                $manager->add($attribute->getEntityTypeId(), $attribute->getAttributeCode(), $_option);
                $result[$value] = $_option;
            } else {
                $_option = $factory->create();
                $_option->setValue($option_id);
                $_option->setLabel($value);
                $result[$value] = $_option;
            }
        }

        return $result;
    }

    private function updateSouthbaySizeOptions($options)
    {
        return $this->updateAttrOptions('southbay_size', $options);
    }

    public function updateSouthbayColorOptions($options)
    {
        return $this->updateAttrOptions('southbay_color', $options);
    }

    private function importProduct($item, $attributeSetId, $southbay_size_attr, $output, $images)
    {
        $current_store = 4;
        $objectManager = $this->getObjectManager();
        $is_parent = false;
        $children = [];

        if (isset($item['parent'])) {
            $children = $item['children'];
            $item = $item['parent'];
            $is_parent = true;
            $output->writeln('Intendo importar producto: ' . $item['sku'] . '. Total de variantes: ' . count($children));
        } else {
            $item['sku'] = $item['sku2'];
        }

        // $_configurableproduct = $configurableproduct->loadByAttribute('sku', $item['sku']);

        /**
         * @var \Magento\Catalog\Model\ProductRepository $prepository
         */
        $prepository = $objectManager->create('Magento\Catalog\Model\ProductRepository');

        try {
            $_configurableproduct = $prepository->get($item['sku'], true, $current_store);
        } catch (NoSuchEntityException $e) {
            $_configurableproduct = null;
            /**
             * @var \Magento\Catalog\Model\Product $configurableproduct
             */
            $configurableproduct = $objectManager->create('Magento\Catalog\Model\Product');
            $configurableproduct->setStoreId($current_store);
        }

        if (empty($_configurableproduct) || is_null($_configurableproduct->getId())) {
            $configurableproduct->setData('url_key', urlencode($item['sku']));
            $configurableproduct->setSku($item['sku']);
            $configurableproduct->setAttributeSetId($attributeSetId);

            if ($is_parent) {
                $configurableproduct->setTypeId('configurable'); // type of product (simple/virtual/downloadable/configurable)
                $configurableproduct->setVisibility(4);  // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
            } else {
                $configurableproduct->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
                $configurableproduct->setVisibility(1);  // visibility of product (Not Visible Individually (1) / Catalog (2)/ Search (3)/ Catalog, Search(4))
            }
        } else {
            $output->writeln('El producto ya existe...');
            $configurableproduct = $_configurableproduct;
        }

        $product_images = [];
        $image_sku = ($is_parent ? $item['sku'] : $item['sku_parent']);

        if (isset($images[$image_sku])) {
            $product_images = $images[$image_sku];
        }

        if (!empty($product_images)) {
            $images_entries = $configurableproduct->getMediaGalleryEntries();

            foreach ($product_images as $imagePath) {
                $info = pathinfo($imagePath);
                $_imagePath = $info['filename'];

                $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($_imagePath);
                $dispersionPath = \Magento\MediaStorage\Model\File\Uploader::getDispersionPath($fileName);
                $fileName = $dispersionPath . '/' . $fileName;

                $found = false;

                /*
                LENTO
                if (!empty($images_entries)) {
                    foreach ($images_entries as $entry) {
                        $file = $entry->getData('file');
                        $output->writeln('file: ' . $file . '. buscando: ' . $fileName);
                        if (str_starts_with($file, $fileName)) {
                            $output->writeln('Ya tenia foto: ' . $fileName);
                            $found = true;
                            break;
                        }
                    }
                }
                */

                if (!$found) {
                    $configurableproduct->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
                }
            }
        }

        $configurableproduct->setName($item['NOMBRE']);
        $configurableproduct->setStatus(1); // status enabled/disabled 1/0
        $configurableproduct->setPrice($item['Precio WH']);
        $configurableproduct->setDescription($item['Descripción'] ?? '');
        $configurableproduct->setTaxClassId(0); // Tax class ID

        // $configurableproduct->setWebsiteIds(array(3)); // set website Id
        if (!$configurableproduct->hasWebsiteIds()) {
            $configurableproduct->setWebsiteIds(array($current_store));
        } else {
            $ids = $configurableproduct->getWebsiteIds();
            if (!in_array($current_store, $ids)) {
                $ids[] = $current_store;
                $configurableproduct->setWebsiteIds($ids);
            }
        }

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
        $configurableproduct->setSouthbayPriceRetail($item['Precio RT']);
        $purchase_unit = $item['UnidadCompra'];
        if (empty($purchase_unit)) {
            $purchase_unit = 1;
        }
        $configurableproduct->setSouthbayPurchaseUnit($purchase_unit);
        $configurableproduct->setSouthbaySport($item['southbay_sport']);
        $configurableproduct->setSouthbaySilueta1($item['southbay_silueta_1']);
        $configurableproduct->setSouthbaySilueta2($item['southbay_silueta_2']);
        $configurableproduct->setSouthbaySize($item['southbay_size']);
        $configurableproduct->setSouthbayColor($item['southbay_color']);
        $configurableproduct->setSouthbaySeasonCode($item['southbay_season_code']);
        $configurableproduct->setSouthbayChannelLevelList($item['southbay_channel_level_list']);

        $configurableproduct->save();

        $product_id = $configurableproduct->getId();
        // $output->writeln('Producto generado id: ' . $product_id);
        // $output->writeln('Data: ' . json_encode($configurableproduct->getData()));

        if ($is_parent) {
            $values = [];
            $associatedProductIds = [];

            foreach ($children as $child) {
                $child['sku_parent'] = $item['sku'];
                $child_product_id = $this->importProduct($child, $attributeSetId, $southbay_size_attr, $output, $images);

                $values[] = [
                    "value_index" => $child['southbay_size'],
                ];

                $associatedProductIds[] = $child_product_id;
            }

            $extensionAttrs = $configurableproduct->getExtensionAttributes();
            $extensionAttrs->setConfigurableProductLinks($associatedProductIds);

            $_optionsFactory = $this->getObjectManager()->get(\Magento\ConfigurableProduct\Helper\Product\Options\Factory::class);

            $optionsFact = $_optionsFactory->create([
                [
                    "attribute_id" => $southbay_size_attr->getId(),
                    "label" => $southbay_size_attr->getFrontendLabel(),
                    "position" => 0,
                    "values" => $values,
                ]
            ]);

            $extensionAttrs->setConfigurableProductOptions($optionsFact);
            $configurableproduct->setExtensionAttributes($extensionAttrs);
            $configurableproduct->save();
            $output->writeln('variantes creadas...');
        }

        return $product_id;
    }

    private function importSeasonTypes(): void
    {
        $factory = $this->getObjectManager()->get(\Southbay\Product\Model\SeasonTypeFactory::class);
        /**
         * @var \Southbay\Product\Model\ResourceModel\SeasonType $repository
         */
        $repository = $this->getObjectManager()->get(\Southbay\Product\Model\ResourceModel\SeasonType::class);
        $types = $this->seasonTypes();

        foreach ($types as $type) {
            try {
                /**
                 * @var \Southbay\Product\Model\SeasonType $model
                 */
                $model = $factory->create();
                $model->setSeasonTypeCode($type['code']);
                $model->setSeasonTypeName($type['name']);
                $repository->save($model);
            } catch (AlreadyExistsException $e) {
            }
        }
    }

    /**
     * @return LoggerInterface
     */
    private function getLog()
    {
        return $this->getObjectManager()->get(LoggerInterface::class);
    }
}
