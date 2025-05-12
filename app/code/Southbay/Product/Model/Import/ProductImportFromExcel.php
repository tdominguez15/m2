<?php

namespace Southbay\Product\Model\Import;

use Magento\Framework\Filesystem;

class ProductImportFromExcel
{
    private $resource;
    private $filesystem;
    private $log;
    private $attribute_set_id;
    private $attribute_ids = [];
    private $connection;
    private $attr_options = [];
    private $attr_options_ids = [];
    private $attr_options_v2 = [];
    private $p;

    private $categories_cache = [];

    public function __construct(Filesystem                                $filesystem,
                                \Magento\Framework\App\ResourceConnection $resource,
                                \Psr\Log\LoggerInterface                  $log)
    {
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
        $this->filesystem = $filesystem;
        $this->log = $log;
        $this->p = new ProductImporter();
    }

    public function import($file, $store_id, $website_id, $country, $category_root)
    {
        $this->log->debug('Start import products...');
        $data = $this->getData($file);
        $this->log->debug('Products to imports', ['count' => count($data)]);

        $this->saveData($data, $store_id, $website_id, $country, $category_root);

        $this->log->debug('End import products');
    }

    private function getData($file)
    {
        $result = [];
        $path = $this->getPath();
        // $file = $path . '/ADDs para NVS URU.xlsx';
        $file = $path . '/' . $file;

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $first = true;

        foreach ($sheet->getRowIterator() as $row) {
            if ($first) {
                $first = false;
                continue;
            }

            $column_index = -1;
            $stop = false;

            $columns = [];

            foreach ($row->getCellIterator() as $cell) {
                $column_index++;
                $value = $cell->getValue();

                if (empty($value) && $column_index === 0) {
                    $stop = true;
                    break;
                }

                $value = trim(strval($value));

                switch ($column_index) {
                    case 0:
                    {
                        $columns['generic'] = $value;
                        break;
                    }
                    case 1:
                    {
                        $columns['variant'] = $value;
                        break;
                    }
                    case 2:
                    {
                        $columns['sku_full'] = $value;
                        break;
                    }
                    case 3:
                    {
                        $columns['sku'] = $value;
                        break;
                    }
                    case 4:
                    {
                        $columns['ean'] = $value;
                        break;
                    }
                    case 5:
                    {
                        $columns['size'] = $value;
                        break;
                    }
                    case 6:
                    {
                        $columns['group'] = $value;
                        break;
                    }
                    case 7:
                    {
                        $columns['season'] = $value;
                        break;
                    }
                    case 8:
                    {
                        $columns['season_year'] = $value;
                        break;
                    }
                    case 9:
                    {
                        $columns['name'] = $value;
                        break;
                    }
                    case 10:
                    {
                        $columns['color'] = $value;
                        break;
                    }
                    case 11:
                    {
                        $columns['initiative'] = $value;
                        break;
                    }
                    case 12:
                    {
                        $columns['starte_date'] = $value;
                        break;
                    }
                    case 19:
                    {
                        $columns['segmentation'] = $value;
                        break;
                    }
                    case 21:
                    {
                        $columns['purchase_unit'] = $value;
                        break;
                    }
                    case 22:
                    {
                        $columns['price_rt'] = round(floatval($value), 2);
                        break;
                    }
                    case 23:
                    {
                        $columns['price_wh'] = round(floatval($value), 2);
                        break;
                    }
                    case 24:
                    {
                        $columns['description'] = $value;
                        break;
                    }
                }
            }

            if ($stop) {
                break;
            }

            if (!isset($result[$columns['sku']])) {
                $result[$columns['sku']] = $columns;
                $result[$columns['sku']]['items'] = [];
            }

            $result[$columns['sku']]['items'][$columns['sku_full']] = $columns;
        }

        return $result;
    }

    private function getPath()
    {
        $media_folder = 'import';
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        return $mediaDirectory->getAbsolutePath($media_folder);
    }

    private function saveData($data, $store_id, $website_id, $country, $category_root)
    {
        /*
        $store_id = 3;
        $website_id = 2;
        $country = 'AR';
        $category_root = 'Temporada SP2025';
        */

        try {
            $this->connection->beginTransaction();

            foreach ($data as $item) {
                $items = $item['items'];

                $parent_id = $this->saveProduct($item, $category_root, $store_id, $website_id, $country);

                foreach ($items as $_item) {
                    $id = $this->saveProduct($_item, $category_root, $store_id, $website_id, $country, $parent_id);
                }
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $this->log->error('Error', ['e' => $e]);
        }
    }

    private function saveProduct($data, $category_root, $store_id, $website_id, $country, $parent_id = null)
    {
        if (is_null($parent_id)) {
            $sku = $data['sku'];
        } else {
            $sku = $data['sku_full'];
        }

        $connection = $this->connection;
        $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku='$sku'";
        $product = $connection->fetchCol($sql);

        $this->log->debug('search product', ['sku' => $sku, 'p' => $product]);

        if (empty($product) || !empty($product['entity_id'])) {
            $attribute_set_id = $this->getAttributeSetId();
            $type = (is_null($parent_id) ? 'configurable' : 'simple');

            $sql = "INSERT INTO catalog_product_entity(attribute_set_id,type_id,sku,has_options,required_options) VALUES($attribute_set_id,'$type','$sku',0,0)";

            $connection->query($sql);

            $sql = "SELECT LAST_INSERT_ID() as id";
            $id = $connection->fetchCol($sql)[0];

            $this->addIntValues($data, $type, $id);
            $this->addDecimalValues($data, $website_id, $id);
            $this->addText($data, $id);
            $this->addVarchart($data, $sku, $type, $country, $id);
            $this->addStockStatus($id);

            if (!is_null($parent_id)) {
                $this->addChild($id, $parent_id);
            } else {
                $this->addParent($id);
            }

            $this->addWebSite($id, $store_id);
            $this->setCategory($category_root, $id);
        } else {
            $id = $product[0];
        }

        return $id;
    }


    private function setCategory($category_root, $id)
    {
        $attr_level_1 = $this->getAttributeId('southbay_department');
        $attr_level_2 = $this->getAttributeId('southbay_gender');
        $attr_level_3 = $this->getAttributeId('southbay_age');
        $attr_level_4 = $this->getAttributeId('southbay_sport');
        $attr_level_5 = $this->getAttributeId('southbay_silueta_1');
        $attr_level_6 = $this->getAttributeId('southbay_silueta_2');

        $sql = "SELECT attribute_id, `value` FROM catalog_product_entity_varchar WHERE entity_id = $id AND attribute_id IN ($attr_level_1,$attr_level_2,$attr_level_3,$attr_level_4,$attr_level_5,$attr_level_6)";

        $list = $this->connection->fetchAll($sql);
        $map = [];

        foreach ($list as $item) {
            $map[$item['attribute_id']] = $item['value'];
        }

        $level_0_text = $category_root;
        $level_1_text = $this->getOptionsIds($attr_level_1)[$map[$attr_level_1]];
        $level_2_text = $this->getOptionsIds($attr_level_2)[$map[$attr_level_2]];
        $level_3_text = $this->getOptionsIds($attr_level_3)[$map[$attr_level_3]];
        $level_4_text = $this->getOptionsIds($attr_level_4)[$map[$attr_level_4]];
        $level_5_text = $this->getOptionsIds($attr_level_5)[$map[$attr_level_5]];
        $level_6_text = $this->getOptionsIds($attr_level_6)[$map[$attr_level_6]];

        $level_0 = $this->getCategoryOrCreate($level_0_text, 0);
        $level_1 = $this->getCategoryOrCreate($level_1_text, 1, $level_0);
        $level_2 = $this->getCategoryOrCreate($level_2_text, 2, $level_0, $level_1);
        $level_3 = $this->getCategoryOrCreate($level_3_text, 3, $level_0, $level_1, $level_2);
        $level_4 = $this->getCategoryOrCreate($level_4_text, 4, $level_0, $level_1, $level_2, $level_3);
        $level_5 = $this->getCategoryOrCreate($level_5_text, 5, $level_0, $level_1, $level_2, $level_3, $level_4);
        $level_6 = $this->getCategoryOrCreate($level_6_text, 6, $level_0, $level_1, $level_2, $level_3, $level_4, $level_5);

        $this->addProductToCatalog($id, $level_1['id']);
        $this->addProductToCatalog($id, $level_2['id']);
        $this->addProductToCatalog($id, $level_3['id']);
        $this->addProductToCatalog($id, $level_4['id']);
        $this->addProductToCatalog($id, $level_5['id']);
        $this->addProductToCatalog($id, $level_6['id']);
    }

    private function addProductToCatalog($id, $catalog_id)
    {
        $sql = "SELECT 1 FROM catalog_category_product WHERE product_id = $id AND category_id = $catalog_id";
        $result = $this->connection->fetchCol($sql);

        if (empty($result)) {
            $sql = "INSERT INTO catalog_category_product(category_id,product_id,position) VALUES($catalog_id,$id,0)";
            $this->connection->query($sql);

            $sql = "UPDATE catalog_category_entity SET children_count = (children_count + 1) WHERE entity_id = $catalog_id";
            $this->connection->query($sql);
        }
    }

    private function getCategoryOrCreate($name,
                                         $level,
                                         $level_0 = null,
                                         $level_1 = null,
                                         $level_2 = null,
                                         $level_3 = null,
                                         $level_4 = null,
                                         $level_5 = null)
    {
        $key = $name . ":" . $level;

        if (!isset($this->categories_cache[$key])) {
            $attr_id = $this->getAttributeId('name', 3);
            $path = '1';

            if ($level > 0) {
                switch ($level) {
                    case 1:
                    {
                        $path = $level_0['path'];
                        break;
                    }
                    case 2:
                    {
                        $path = $level_1['path'];
                        break;
                    }
                    case 3:
                    {
                        $path = $level_2['path'];
                        break;
                    }
                    case 4:
                    {
                        $path = $level_3['path'];
                        break;
                    }
                    case 5:
                    {
                        $path = $level_4['path'];
                        break;
                    }
                    case 6:
                    {
                        $path = $level_5['path'];
                        break;
                    }
                }
            }

            $sql = "SELECT c.entity_id as id FROM catalog_category_entity c INNER JOIN catalog_category_entity_varchar v ON v.entity_id = c.entity_id WHERE v.attribute_id = $attr_id AND v.store_id = 0 AND v.`value` = '$name' AND c.path REGEXP '^($path/)\\\\d+$'";

            $id = $this->connection->fetchCol($sql);

            if (empty($id)) {
                if ($level == 0) {
                    throw new \Exception('Category root dose not exists');
                }
                $attr_set_id = $this->getAttributeSetId();
                $parent_id = -1;
                $parent_url_key = '';
                $parent_url_path = '';

                switch ($level) {
                    case 1:
                    {
                        $parent_id = $level_0['id'];
                        $parent_url_key = $level_0['url_key'];
                        $parent_url_path = $level_0['url_path'];
                        break;
                    }
                    case 2:
                    {
                        $parent_id = $level_1['id'];
                        $parent_url_key = $level_1['url_key'];
                        $parent_url_path = $level_1['url_path'];
                        break;
                    }
                    case 3:
                    {
                        $parent_id = $level_2['id'];
                        $parent_url_key = $level_2['url_key'];
                        $parent_url_path = $level_2['url_path'];
                        break;
                    }
                    case 4:
                    {
                        $parent_id = $level_3['id'];
                        $parent_url_key = $level_3['url_key'];
                        $parent_url_path = $level_3['url_path'];
                        break;
                    }
                    case 5:
                    {
                        $parent_id = $level_4['id'];
                        $parent_url_key = $level_4['url_key'];
                        $parent_url_path = $level_4['url_path'];
                        break;
                    }
                    case 6:
                    {
                        $parent_id = $level_5['id'];
                        $parent_url_key = $level_5['url_key'];
                        $parent_url_path = $level_5['url_path'];
                        break;
                    }
                }

                $sql = "INSERT INTO catalog_category_entity(attribute_set_id,parent_id,path,position,level,children_count) VALUES($attr_set_id,$parent_id,'',1,$level,0)";

                $this->connection->query($sql);

                $sql = "SELECT LAST_INSERT_ID() as id";
                $id = $this->connection->fetchCol($sql)[0];

                $path .= '/' . $id;
                $sql = "UPDATE catalog_category_entity SET path = '$path' WHERE entity_id = $id";
                $this->connection->query($sql);

                $sql = "INSERT INTO catalog_category_entity_varchar(attribute_id, store_id, entity_id, value) VALUES($attr_id,0,$id,'$name')";
                $this->connection->query($sql);

                $attr_id = $this->getAttributeId('url_key', 3);
                $url_key = $parent_url_key . '-' . str_replace(strtolower($name), ' ', '-');
                $sql = "INSERT INTO catalog_category_entity_varchar(attribute_id, store_id, entity_id, value) VALUES($attr_id,0,$id,'$url_key')";
                $this->connection->query($sql);

                $attr_id = $this->getAttributeId('url_path', 3);
                $url_path = $parent_url_path . '-' . str_replace(strtolower($name), ' ', '-');
                $sql = "INSERT INTO catalog_category_entity_varchar(attribute_id, store_id, entity_id, value) VALUES($attr_id,0,$id,'$url_path')";
                $this->connection->query($sql);

                $attr_id = $this->getAttributeId('is_active', 3);
                $sql = "INSERT INTO catalog_category_entity_int(attribute_id, store_id, entity_id, value) VALUES($attr_id,0,$id,1)";
                $this->connection->query($sql);

                $attr_id = $this->getAttributeId('is_anchor', 3);
                $sql = "INSERT INTO catalog_category_entity_int(attribute_id, store_id, entity_id, value) VALUES($attr_id,0,$id,1)";
                $this->connection->query($sql);

                $attr_id = $this->getAttributeId('include_in_menu', 3);
                $sql = "INSERT INTO catalog_category_entity_int(attribute_id, store_id, entity_id, value) VALUES($attr_id,0,$id,1)";
                $this->connection->query($sql);

                $this->categories_cache[$key] = [
                    'id' => $id,
                    'path' => $path,
                    'url_key' => $url_key,
                    'url_path' => $url_path
                ];
            } else {
                $id = $id[0];
                $attr_id = $this->getAttributeId('url_key', 3);
                $sql = "SELECT `value` FROM catalog_category_entity_varchar WHERE entity_id = $id AND attribute_id = $attr_id AND store_id = 0";
                $url_key = $this->connection->fetchCol($sql)[0];

                $attr_id = $this->getAttributeId('url_path', 3);
                $sql = "SELECT `value` FROM catalog_category_entity_varchar WHERE entity_id = $id AND attribute_id = $attr_id AND store_id = 0";
                $url_path = $this->connection->fetchCol($sql)[0];

                $this->categories_cache[$key] = [
                    'id' => $id,
                    'path' => $path . '/' . $id,
                    'url_key' => $url_key,
                    'url_path' => $url_path
                ];
            }
        }

        return $this->categories_cache[$key];
    }

    private function addWebSite($id, $website_id)
    {
        $sql = "INSERT INTO catalog_product_website(product_id, website_id) VALUES($id, $website_id)";
        $this->connection->query($sql);
    }

    private function addParent($parent_id)
    {
        $attr_id = $this->getAttributeId('southbay_size');
        $sql = "INSERT INTO catalog_product_super_attribute(product_id, attribute_id, position) VALUES($parent_id, $attr_id,0)";
        $this->connection->query($sql);

        $sql = "SELECT LAST_INSERT_ID() as id";
        $id = $this->connection->fetchCol($sql)[0];

        $sql = "INSERT INTO catalog_product_super_attribute_label(product_super_attribute_id,store_id,use_default,value) VALUES($id,0,0,'Talle')";
        $this->connection->query($sql);
    }

    private function addChild($id, $parent_id)
    {
        $sql = "INSERT INTO catalog_product_relation(parent_id, child_id) VALUES($parent_id, $id)";
        $this->connection->query($sql);

        $sql = "INSERT INTO catalog_product_super_link(parent_id, product_id) VALUES($parent_id, $id)";
        $this->connection->query($sql);
    }

    private function addVarchart($data, $sku, $type, $country, $id)
    {
        $attr_id = $this->getAttributeId('name');
        $value = $data['name'];
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('options_container');
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'container2')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('url_key');
        $value = strtolower(str_replace('/', '-', $sku));
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        if ($type == 'configurable') {
            $attr_id = $this->getAttributeId('url_path');
            $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
            $this->connection->query($sql);
        }

        $attr_id = $this->getAttributeId('msrp_display_actual_price_type');
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'0')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('gift_message_available');
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'0')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_season_code');
        $value = $data['season_year'];
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_channel_level_list', 1);
        $value = $data['segmentation'];

        if (!empty($value)) {
            $parts = explode(';', $value);
            $str = '';
            foreach ($parts as $p) {
                if (!str_contains($p, $str)) {
                    $str .= ";$country:$p;";
                }
            }
            $value = $str;
        }

        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_variant');
        if ($type == 'configurable') {
            $value = $data['generic'];
        } else {
            $value = $data['variant'];
        }
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $group = $data['group'];
        $department = substr($group, 0, 1);
        $gender = substr($group, 1, 2);
        $age = substr($group, 3, 2);
        $firstSilueta = substr($group, 5, 2);
        $sport = substr($group, 7, 2);
        $secondSilueta = substr($group, 10, 2);

        $department_options = $this->transformAttrOptions('southbay_department');
        $gender_options = $this->transformAttrOptions('southbay_gender');
        $age_options = $this->transformAttrOptions('southbay_age');
        $sport_options = $this->transformAttrOptions('southbay_sport');
        $first_silueta_options = $this->transformAttrOptions('southbay_silueta_1');
        $second_silueta_options = $this->transformAttrOptions('southbay_silueta_2');

        // $departments = $this->p->departmentCodes();
        $departmentsText = $this->p->departmentCodesText();
        // $genders = $this->p->genderCodes();
        $gendersText = $this->p->genderCodesText();
        // $ages = $this->p->ageCodes();
        $agesText = $this->p->ageCodesText();
        // $firstSiluetaList = $this->p->firstSiluetaCodes();
        $firstSiluetaTextList = $this->p->firstSiluetaCodesText();
        // $sports = $this->p->sportCodes();
        $sportsText = $this->p->sportCodesText();
        // $secondSiluetaList = $this->p->secondSilueta();
        $secondSiluetaTextList = $this->p->secondSiluetaText();

        $attr_id = $this->getAttributeId('southbay_department');
        $value = $department_options[$departmentsText[$department]]->getValue();
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_gender');
        $value = $gender_options[$gendersText[$gender]]->getValue();
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_age');
        $value = $age_options[$agesText[$age]]->getValue();
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_sport');
        $value = $sport_options[$sportsText[$sport]]->getValue();
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_silueta_1');
        $value = $first_silueta_options[$firstSiluetaTextList[$firstSilueta]]->getValue();
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_silueta_2');
        $value = $second_silueta_options[$secondSiluetaTextList[$secondSilueta]]->getValue();
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_size');
        $value = $this->getOptionOrCreate($attr_id, $data['size']);
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,$value)";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_color');
        $value = $this->getOptionOrCreate($attr_id, $data['color']);
        $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,$value)";
        $this->connection->query($sql);
    }

    private function addText($data, $id)
    {
        $attr_id = $this->getAttributeId('description');
        $value = $data['description'];
        $sql = "INSERT INTO catalog_product_entity_text(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,'$value')";
        $this->connection->query($sql);
    }

    private function addDecimalValues($data, $website_id, $id)
    {
        $attr_id = $this->getAttributeId('price');
        $value = $data['price_wh'];
        $sql = "INSERT INTO catalog_product_entity_decimal(attribute_id,store_id,entity_id,value) VALUES($attr_id,$website_id,$id,$value)";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_price_retail');
        $value = $data['price_rt'];
        $sql = "INSERT INTO catalog_product_entity_decimal(attribute_id,store_id,entity_id,value) VALUES($attr_id,$website_id,$id,$value)";
        $this->connection->query($sql);
    }

    private function addStockStatus($id)
    {
        $sql = "INSERT INTO cataloginventory_stock_item(product_id,stock_id,qty,min_qty,use_config_min_qty,is_qty_decimal,backorders,use_config_backorders, min_sale_qty,use_config_min_sale_qty, max_sale_qty,use_config_max_sale_qty,is_in_stock,use_config_notify_stock_qty,manage_stock,use_config_manage_stock,stock_status_changed_auto,use_config_qty_increments,qty_increments,use_config_enable_qty_inc,enable_qty_increments,is_decimal_divided,website_id) VALUES($id,1,null,0,1,0,0,1,0,1,0,1,0,1,0,0,0,1,0,1,0,0,0)";

        $this->connection->query($sql);

        $sql = "INSERT INTO cataloginventory_stock_status(product_id, website_id,stock_id,qty,stock_status) VALUES($id,0,1,0,1)";
        $this->connection->query($sql);
    }

    private function addIntValues($data, $type, $id)
    {
        $attr_id = $this->getAttributeId('status');
        $sql = "INSERT INTO catalog_product_entity_int(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,1)";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('visibility');
        $value = 1;
        if ($type == 'configurable') {
            $value = 4;
        }

        $sql = "INSERT INTO catalog_product_entity_int(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,$value)";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('quantity_and_stock_status');
        $sql = "INSERT INTO catalog_product_entity_int(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,1)";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('tax_class_id');
        $sql = "INSERT INTO catalog_product_entity_int(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,0)";
        $this->connection->query($sql);

        $attr_id = $this->getAttributeId('southbay_purchase_unit');
        $value = $data['purchase_unit'];
        $sql = "INSERT INTO catalog_product_entity_int(attribute_id,store_id,entity_id,value) VALUES($attr_id,0,$id,$value)";
        $this->connection->query($sql);
    }

    private function getAttributeSetId()
    {
        if (!isset($this->attribute_set_id)) {
            $connection = $this->connection;
            $sql = "SELECT attribute_set_id FROM eav_attribute_set WHERE attribute_set_name = 'Southbay attr set'";
            $result = $connection->fetchCol($sql);

            $this->attribute_set_id = $result[0];
        }

        return $this->attribute_set_id;
    }

    private function getAttributeId($code, $type = 4)
    {
        $key = $code . ':' . $type;
        if (!isset($this->attribute_ids[$key])) {
            $connection = $this->connection;
            $sql = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = '$code' AND entity_type_id = $type";
            $result = $connection->fetchCol($sql);

            $this->attribute_ids[$key] = $result[0];
        }

        return $this->attribute_ids[$key];
    }

    private function transformAttrOptions($code)
    {
        if (!isset($this->attr_options[$code])) {
            $this->attr_options[$code] = $this->p->transformAttrOptions($code);
        }

        return $this->attr_options[$code];
    }

    private function getOptionsIds($attr_id)
    {
        if (!isset($this->attr_options_ids[$attr_id])) {
            $sql = "SELECT o.option_id, v.value FROM eav_attribute_option o INNER JOIN eav_attribute_option_value v ON v.option_id = o.option_id WHERE o.attribute_id = $attr_id";
            $list = $this->connection->fetchAll($sql);
            $options = [];
            foreach ($list as $item) {
                $options[$item['option_id']] = $item['value'];
            }
            $this->attr_options_ids[$attr_id] = $options;
        } else {
            $options = $this->attr_options_ids[$attr_id];
        }

        return $options;
    }

    private function getOptionOrCreate($attr_id, $value)
    {
        if (!isset($this->attr_options_v2[$attr_id])) {
            $sql = "SELECT o.option_id, v.value FROM eav_attribute_option o INNER JOIN eav_attribute_option_value v ON v.option_id = o.option_id WHERE o.attribute_id = $attr_id";
            $list = $this->connection->fetchAll($sql);
            $options = [];
            foreach ($list as $item) {
                $options[$item['value']] = $item['option_id'];
            }
            $this->attr_options_v2[$attr_id] = $options;
        } else {
            $options = $this->attr_options_v2[$attr_id];
        }

        if (isset($options[$value])) {
            return $options[$value];
        } else {
            $sql = "INSERT INTO eav_attribute_option(attribute_id, sort_order) VALUES($attr_id, 0)";
            $this->connection->query($sql);

            $sql = "SELECT LAST_INSERT_ID() as id";
            $id = $this->connection->fetchCol($sql)[0];

            $sql = "INSERT INTO eav_attribute_option_value(option_id, store_id, value) VALUES($id, 0, '$value')";
            $this->connection->query($sql);

            unset($this->attr_options_v2[$attr_id]);

            return $id;
        }
    }
}
