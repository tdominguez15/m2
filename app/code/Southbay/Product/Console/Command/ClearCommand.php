<?php

namespace Southbay\Product\Console\Command;

use Southbay\Product\Model\Import\ProductImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCommand extends Command
{
    private OutputInterface $output;

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:products:add-img')->setDescription('add img');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $file = __DIR__ . '/add-images.json';
        $output->writeln('reading file: ' . $file);

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        $output->writeln('total data: ' . count($data));
        $p = new ProductImporter();

        $images = $p->groupImages();
        $target_path = $this->getTargetPath();

        $output->writeln('total imgs: ' . count($images));
        $output->writeln('target_path: ' . $target_path);

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Psr\Log\LoggerInterface $log
         */
        $log = $objectManager->get('Psr\Log\LoggerInterface');

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();

        $procesados = [];

        foreach ($data as $sku) {
            if (!isset($procesados[$sku])) {
                $procesados[$sku] = true;

                if (isset($images[$sku])) {
                    $this->updateImages($sku, $target_path, $images[$sku], $connection);
                }
            }
        }

        return 1;
    }

    private function updateImages($sku, $target_path, $images, \Magento\Framework\DB\Adapter\AdapterInterface $connection)
    {
        $sql = "SELECT entity_id FROM `catalog_product_entity` WHERE sku = '$sku'";
        // $this->output->writeln($sql);
        $ids = $connection->fetchAll($sql);

        if (empty($ids)) {
            // $this->output->writeln('No existe el sku: ' . $sku);
            return;
        }

        foreach ($ids as $id) {
            $entity_id = $id['entity_id'];

            $sql = "DELETE FROM catalog_product_entity_media_gallery_value WHERE entity_id = $entity_id";
            // $this->output->writeln($sql);
            // rel img con producto
            $connection->query($sql);

            $sql = "DELETE FROM catalog_product_entity_media_gallery WHERE value_id in (SELECT value_id FROM catalog_product_entity_media_gallery_value_to_entity WHERE entity_id = $entity_id) and attribute_id = 90";
            // $this->output->writeln($sql);
            // imagenes
            $connection->query($sql);

            $sql = "DELETE FROM catalog_product_entity_media_gallery_value_to_entity WHERE entity_id = $entity_id";
            // $this->output->writeln($sql);
            // posicion de cada img regun rel img producto
            $connection->query($sql);

            $sql = "DELETE FROM catalog_product_entity_varchar WHERE entity_id = $entity_id AND attribute_id IN (87,88,89)";
            // $this->output->writeln($sql);
            // elimina los atributos de base, etc
            $connection->query($sql);
        }

        $imgs_data = [];
        foreach ($images as $img) {
            $real_target_path = $target_path . '/';
            // $this->output->writeln($real_target_path);

            $filename = basename($img);
            $first = substr($filename, 0, 1);
            $second = substr($filename, 1, 1);

            $real_target_path .= $first;

            if (!file_exists($real_target_path)) {
                // $this->output->writeln($real_target_path);
                mkdir($real_target_path);
            }

            $real_target_path .= '/' . $second;

            if (!file_exists($real_target_path)) {
                // $this->output->writeln($real_target_path);
                mkdir($real_target_path);
            }

            $real_target_path .= '/' . $filename;

            if (!file_exists($real_target_path)) {
                // $this->output->writeln($real_target_path);
                copy($img, $real_target_path);
            }

            $sql = "INSERT INTO catalog_product_entity_media_gallery(attribute_id, value, media_type, disabled) VALUES(90, '/$first/$second/$filename','image',0)";
            // $this->output->writeln($sql);
            $connection->query($sql);

            $sql = 'SELECT LAST_INSERT_ID() as id';
            // $this->output->writeln($sql);
            $last = $connection->fetchRow($sql);

            $id = $last['id'];
            $imgs_data[] = ['id' => $id, 'file' => $filename, 'file2' => "/$first/$second/$filename"];
        }

        foreach ($ids as $id) {
            $position = 1;
            $first = true;
            $first_img = null;
            foreach ($imgs_data as $img) {
                $entity_id = $id['entity_id'];
                $img_id = $img['id'];

                $sql = "INSERT INTO catalog_product_entity_media_gallery_value_to_entity (value_id, entity_id) VALUES($img_id, $entity_id)";
                // $this->output->writeln($sql);
                $connection->query($sql);

                $filename2 = $img['file2'];

                if ($first) {
                    if (is_null($first_img)) {
                        $first_img = $filename2;
                    }
                    if (str_contains($filename2, 'PV.')) {
                        $_position = 1;
                    } else if (str_contains($filename2, 'PHSLH000')) {
                        $_position = 1;
                    } else if (str_contains($filename2, 'PHSYM')) {
                        $_position = 1;
                    } else {
                        if ($position == 1) {
                            $position++;
                        }
                        $_position = $position;
                    }
                } else {
                    $_position = $position;
                }

                if ($_position == 1 && $first) {
                    $first = false;
                    $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,entity_id, store_id,`value`) VALUES(87, $entity_id,0,'$filename2'), (88, $entity_id,0,'$filename2'), (89, $entity_id,0,'$filename2')";
                    // $this->output->writeln($sql);
                    $connection->query($sql);
                }

                $sql = "INSERT INTO catalog_product_entity_media_gallery_value(value_id, entity_id, store_id, label, position, disabled) VALUES($img_id,  $entity_id, 0, null, $position, 0)";
                // $this->output->writeln($sql);
                $connection->query($sql);

                if ($_position > 1) {
                    $position++;
                }
            }

            if ($first && !is_null($first_img)) {
                $sql = "INSERT INTO catalog_product_entity_varchar(attribute_id,entity_id, store_id,`value`) VALUES(87, $entity_id,0,'$first_img'), (88, $entity_id,0,'$first_img'), (89, $entity_id,0,'$first_img')";
                // $this->output->writeln($sql);
                $connection->query($sql);
            }
        }
    }

    public function getTargetPath()
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Magento\Framework\Filesystem\DirectoryList $dir
         */
        $dir = $objectManager->get('Magento\Framework\Filesystem\DirectoryList');
        return $dir->getRoot() . '/pub/media/catalog/product';
    }
}
