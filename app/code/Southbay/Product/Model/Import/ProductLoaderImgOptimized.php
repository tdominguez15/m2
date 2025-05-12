<?php

namespace Southbay\Product\Model\Import;

use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\Product\Api\Data\SouthbayProduct;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

class ProductLoaderImgOptimized
{
    const FINAL_PATH = 'fotos';
    const CATALOG_PRODUCT_PATH = 'catalog/product';

    private $repository;
    private $productFactory;
    private $productOptionFactory;
    private $resource;
    private $log;
    private $filesystem;
    private $productAttrLoader;
    private $state;
    private $storeManager;
    private $mapCountryRepository;
    private $getSourceItemsBySku;
    private $sourceItemsSave;
    private $sourceItemFactory;
    private $productRepository;
    private $productHelper;
    private $getStockBySalesChannel;
    private $salesChannelFactory;
    private $configStoreRepository;
    private $configurableProductType;
    private $productManager;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository                         $repository,
        \Magento\Catalog\Model\ProductFactory                            $productFactory,
        \Magento\ConfigurableProduct\Helper\Product\Options\Factory      $productOptionFactory,
        \Magento\Framework\App\ResourceConnection                        $resource,
        Filesystem                                                       $filesystem,
        ProductAttrLoader                                                $productAttrLoader,
        State                                                            $state,
        StoreManagerInterface                                            $storeManager,
        MapCountryRepository                                             $mapCountryRepository,
        GetSourceItemsBySkuInterface                                     $getSourceItemsBySku,
        SourceItemsSaveInterface                                         $sourceItemsSave,
        SourceItemInterfaceFactory                                       $sourceItemFactory,
        ProductRepositoryInterface                                       $productRepository,
        \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface   $getStockBySalesChannel,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory $salesChannelFactory,
        \Southbay\Product\Helper\Data                                    $productHelper,
        \Southbay\CustomCustomer\Model\ConfigStoreRepository             $configStoreRepository,
        ConfigurableProductType                                          $configurableProductType,
        ProductManager                                                   $productManager,
        \Psr\Log\LoggerInterface                                         $log
    )
    {
        $this->repository = $repository;
        $this->productFactory = $productFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->resource = $resource;
        $this->filesystem = $filesystem;
        $this->log = $log;
        $this->productAttrLoader = $productAttrLoader;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->mapCountryRepository = $mapCountryRepository;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->configStoreRepository = $configStoreRepository;
        $this->configurableProductType = $configurableProductType;
        $this->productManager = $productManager;
    }

    public function updateImgBySku($sku, $images)
    {
        $target = $this->getMediaFolder();
        $connection = $this->resource->getConnection();
        try {
            $this->updateImg($sku, $target, $images, $connection);
        } catch (\Exception $e) {
            $this->log->error('error updating product image: ', ['e' => $e->getMessage()]);
        }
    }

    public function updateImg($sku, $target_path, $images, \Magento\Framework\DB\Adapter\AdapterInterface $connection)
    {
        $attr_type_image = $this->productAttrLoader->findAttr('image');
        $attr_type_small_image = $this->productAttrLoader->findAttr('small_image');
        $attr_type_thumbnail = $this->productAttrLoader->findAttr('thumbnail');
        $attr_media_gallery = $this->productAttrLoader->findAttr('media_gallery');

        $attr_type_image_id = $attr_type_image->getAttributeId();
        $attr_type_small_image_id = $attr_type_small_image->getAttributeId();
        $attr_type_thumbnail_id = $attr_type_thumbnail->getAttributeId();
        $attr_media_gallery_id = $attr_media_gallery->getAttributeId();

        // Obtener IDs de productos por SKU
        $sql = "SELECT entity_id FROM `catalog_product_entity` WHERE sku LIKE '$sku%'";
        $ids = $connection->fetchAll($sql);
        $images = $this->sortImagesByMainCriteria($images);
        if (empty($ids) && !is_array($images) ) {
            return;
        }

        foreach ($ids as $id) {
            $entity_id = $id['entity_id'];

            // Borrar imágenes anteriores
            $connection->query("DELETE FROM catalog_product_entity_media_gallery_value WHERE entity_id = $entity_id");
            $connection->query("DELETE FROM catalog_product_entity_media_gallery WHERE value_id IN (SELECT value_id FROM catalog_product_entity_media_gallery_value_to_entity WHERE entity_id = $entity_id) AND attribute_id = $attr_media_gallery_id");
            $connection->query("DELETE FROM catalog_product_entity_media_gallery_value_to_entity WHERE entity_id = $entity_id");
            $connection->query("DELETE FROM catalog_product_entity_varchar WHERE entity_id = $entity_id AND attribute_id IN ($attr_type_image_id, $attr_type_small_image_id, $attr_type_thumbnail_id)");
        }

        $imgs_data = [];


        foreach ($images as $image) {
            foreach ($image as $img) {
                try {
                $real_target_path = $target_path . '/';
                $filename = basename($img);
                $first = substr($filename, 0, 1);
                $second = substr($filename, 1, 1);

                // Crear directorios si no existen
                $real_target_path .= $first;
                if (!file_exists($real_target_path)) {
                    mkdir($real_target_path);
                    chmod($real_target_path, 0755);
                }

                $real_target_path .= '/' . $second;
                if (!file_exists($real_target_path)) {
                    mkdir($real_target_path);
                    chmod($real_target_path, 0755);
                }

                $real_target_path .= '/' . $filename;
                copy($img, $real_target_path);
           //     chmod($real_target_path, 0755);

                // Guarda cada imagen en la galería
                $connection->query("INSERT INTO catalog_product_entity_media_gallery(attribute_id, value, media_type, disabled) VALUES($attr_media_gallery_id, '/$first/$second/$filename', 'image', 0)");

                $sql = 'SELECT LAST_INSERT_ID() as id';
                $last = $connection->fetchRow($sql);

                $img_id = $last['id'];
                $imgs_data[] = [
                    'id' => $img_id,
                    'file' => $filename,
                    'file2' => "/$first/$second/$filename",
                    'is_base' => str_contains($filename, '__BASE__'),
                    'is_small' => str_contains($filename, '__SMALL__'),
                    'is_thumbnail' => str_contains($filename, '__THUMBNAIL__'),
                    'is_excel' => str_contains($filename, '__EXCEL__')
                ];
                } catch (\Exception $e) {
                    $this->log->error("Error al procesar imagen {$img}: " . $e->getMessage(), [
                        'target_path' => $real_target_path,
                        'file_exists' => file_exists($img) ? 'Sí' : 'No',
                        'file_size' => file_exists($img) ? filesize($img) . ' bytes' : 'N/A',
                        'is_readable' => is_readable($img) ? 'Sí' : 'No',
                        'is_writable' => is_writable($real_target_path) ? 'Sí' : 'No',
                    ]);
                }
            }
        }


        foreach ($ids as $id) {
            $entity_id = $id['entity_id'];
            $position = 1;
            $first = true;

            foreach ($imgs_data as $key => $img) {
                //las imagenes se iteran una vez por cada imagen original
                if ($img['is_base'] == false) {
                    continue;
                }

                // Obtener IDs de imágenes adicionales (small y thumbnail)
                $img_id = $img['id'];
                $imgSmallId = $imgs_data[$key + 1]['id'] ?? null;
                $imgThumbnailId = $imgs_data[$key + 2]['id'] ?? null;

                // Nombres de archivo para cada tipo de imagen
                $filename2 = $img['file2'];
                $filenameSmall = str_replace("__BASE__", "__SMALL__", $filename2);
                $filenameThumbnail = str_replace("__BASE__", "__THUMBNAIL__", $filename2);

                // Asociar solo la imagen principal con el producto
                $connection->query("INSERT INTO catalog_product_entity_media_gallery_value_to_entity (value_id, entity_id) VALUES ($img_id, $entity_id)");

                // Asociar small y thumbnail solo de la imagen principal
                if ($first) {
                    if ($imgSmallId && $imgThumbnailId) {
                        $connection->query("INSERT INTO catalog_product_entity_media_gallery_value_to_entity (value_id, entity_id) VALUES ($imgSmallId, $entity_id)");
                        $connection->query("INSERT INTO catalog_product_entity_media_gallery_value_to_entity (value_id, entity_id) VALUES ($imgThumbnailId, $entity_id)");
                    }

                    // Insertar en la tabla 'catalog_product_entity_varchar' solo para la imagen principal, se les asigna que son imagenes de tipo BASE, SMALL y Thumbnail
                    $connection->query(
                        "INSERT INTO catalog_product_entity_varchar(attribute_id, entity_id, store_id, value)
                 VALUES ($attr_type_image_id, $entity_id, 0, '$filename2'),
                        ($attr_type_small_image_id, $entity_id, 0, '$filenameSmall'),
                        ($attr_type_thumbnail_id, $entity_id, 0, '$filenameThumbnail')"
                    );
                }

                // Insertar la imagen principal en 'catalog_product_entity_media_gallery_value' para asignar una posición a cada imagen.
                $connection->query("INSERT INTO catalog_product_entity_media_gallery_value (value_id, entity_id, store_id, label, position, disabled) VALUES ($img_id, $entity_id, 0, NULL, $position, 0)");
                $position++;

                // Insertar imagen small y thumbnail solo si son la principal (no deshabilitadas para la primera imagen).
                if ($first && $imgSmallId && $imgThumbnailId) {
                    $first = false;
                    $connection->query("INSERT INTO catalog_product_entity_media_gallery_value (value_id, entity_id, store_id, label, position, disabled) VALUES ($imgSmallId, $entity_id, 0, NULL, $position, 1)");
                    $position++;
                    $connection->query("INSERT INTO catalog_product_entity_media_gallery_value (value_id, entity_id, store_id, label, position, disabled) VALUES ($imgThumbnailId, $entity_id, 0, NULL, $position, 1)");
                    $position++;
                }
            }
        }
    }


    public function getImgFolder()
    {
        $media_folder = self::FINAL_PATH;
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        if (!$mediaDirectory->isExist($media_folder)) {
            $mediaDirectory->create($media_folder);
        }

        return $mediaDirectory->getAbsolutePath($media_folder);
    }

    public function getMediaFolder()
    {
        $media_folder = self::CATALOG_PRODUCT_PATH;
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        return $mediaDirectory->getAbsolutePath($media_folder);
    }

    /**
     * Ordena un array de imágenes para priorizar las principales.
     *
     * @param array $images Un array de imágenes con la clave 'base'.
     * @return array El array de imágenes ordenado, con las principales primero.
     */
    private function sortImagesByMainCriteria(array $images): array
    {
        usort($images, function ($imageA, $imageB) {
            if (!isset($imageA['base']) || !isset($imageB['base'])) {
                return 0;
            }

            $filenameA = basename($imageA['base']);
            $filenameB = basename($imageB['base']);
            // Determinar si la imagen es la principal basándonos en los criterios
            // $isMainA = (strpos($filenameA, 'PV__') !== false || strpos($filenameA, 'SPSLH000') !== false || strpos($filenameA, 'PHSLH000') !== false || strpos($filenameA, 'PHSYM') !== false || strpos($filenameA, 'PHSFM') !== false);
            // $isMainB = (strpos($filenameB, 'PV__') !== false || strpos($filenameB, 'SPSLH000') !== false || strpos($filenameB, 'PHSLH000') !== false || strpos($filenameB, 'PHSYM') !== false || strpos($filenameB, 'PHSFM') !== false);
            $isMainA = $this->isMainImg($filenameA);
            $isMainB = $this->isMainImg($filenameB);

            return $isMainB <=> $isMainA;
        });

        return $images;
    }

    private function isMainImg($filename)
    {
        $codes = [
            'PV__',
            'SPSLH000',
            'PHSLH000',
            'PHSYM',
            'PHSF',
            'VPSRH001',
            'VPSFH001'
        ];

        $result = false;

        foreach ($codes as $code) {
            if (str_contains($filename, $code)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

//    public function findImgBySku($sku, $connection)
//    {
//        $target = $this->getMediaFolder();
//        $img_folder = $this->getImgFolder();
//
//        $list = glob($img_folder . "/*$sku*.*", GLOB_NOSORT);
//
//        if (!empty($list) && is_array($list)) {
//            $list = [$list];
//        }
//
//        $this->updateImg($sku, $target, $list, $connection);
//    }

    public function findImgBySku($sku, $connection)
    {
        $target = $this->getMediaFolder();
        $img_folder = $this->getImgFolder();

        // Buscar todas las imágenes relacionadas con el SKU
        $files = glob($img_folder . "/*$sku*.*", GLOB_NOSORT);

        $groupedImages = [];
        $finalList = [];

        if (!empty($files) && is_array($files)) {
            foreach ($files as $file) {
                $fileName = basename($file);

                $baseName = preg_replace('/__(BASE|SMALL|THUMBNAIL|EXCEL)__/', '', $fileName);

                // Inicializar grupo si no existe
                if (!isset($groupedImages[$baseName])) {
                    $groupedImages[$baseName] = [
                        'base' => null,
                        'small' => null,
                        'thumbnail' => null,
                        'excel' => null,
                    ];
                }

                if (str_contains($fileName, '__BASE__')) {
                    $groupedImages[$baseName]['base'] = $file;
                } elseif (str_contains($fileName, '__SMALL__')) {
                    $groupedImages[$baseName]['small'] = $file;
                } elseif (str_contains($fileName, '__THUMBNAIL__')) {
                    $groupedImages[$baseName]['thumbnail'] = $file;
                } elseif (str_contains($fileName, '__EXCEL__')) {
                    $groupedImages[$baseName]['excel'] = $file;
                }
            }
        }

        foreach ($groupedImages as $images) {
            if ($images['base'] || $images['small'] || $images['thumbnail'] || $images['excel']) {
                $finalList[] = $images;
            }
        }

        // Actualizar imágenes si hay resultados
        if (!empty($finalList)) {
            $this->updateImg($sku, $target, $finalList, $connection);
        }
    }


}
