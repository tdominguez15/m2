<?php

namespace Southbay\Product\Cron;

use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\Product\Api\Data\SouthbayProductImportImgHistoryInterface;
use Southbay\Product\Model\Import\ProductLoaderImgOptimized;
use Magento\Framework\Image\Factory as ImageFactory;
use Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig;

class SouthbayProductImportImgCronOptimized
{
    const UPLOAD_PATH = 'product/import/img';

    private $log;
    private $collectionFactory;

    private $repository;

    private $filesystem;

    private $scopeConfig;

    private $storeManager;

    private $productLoader;

    private $imageFactory;

    private $checkCustomIndexerConfig;

    public function __construct(\Psr\Log\LoggerInterface                                                                $log,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory\CollectionFactory $collectionFactory,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory                   $repository,
                                Filesystem                                                                              $filesystem,
                                \Magento\Framework\App\Config\ScopeConfigInterface                                      $scopeConfig,
                                StoreManagerInterface                                                                   $storeManager,
                                ProductLoaderImgOptimized                                                               $productLoader,
                                ImageFactory                                                                            $imageFactory,
                                CheckCustomIndexerConfig                                                                $checkCustomIndexerConfig
    )
    {
        $this->log = $log;
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productLoader = $productLoader;
        $this->imageFactory = $imageFactory;
        $this->checkCustomIndexerConfig = $checkCustomIndexerConfig;
    }

    public function run()
    {
        if ($this->checkCustomIndexerConfig->isRunning()) {
            $this->log->warning('Indexer is running');
            return;
        }

        $items = $this->load();
        $ok = false;

        $this->checkCustomIndexerConfig->startUpdateProducts();

        /**
         * @var SouthbayProductImportImgHistoryInterface $item
         */
        foreach ($items as $item) {
            $this->import($item);
            if ($item->getStatus() == SouthbayProductImportImgHistoryInterface::STATUS_END) {
                $ok = true;
            }
        }

        $this->checkCustomIndexerConfig->stopUpdateProducts();

        if ($ok) {
            SouthbayProductImportCron::cacheClean($this->log, 'full_page');
            SouthbayProductImportCron::purgeVarnish($this->log, $this->scopeConfig);
        }
    }

    private function import(SouthbayProductImportImgHistoryInterface $item)
    {
        $ok = false;
        $msg = '';

        $item->setStatus(SouthbayProductImportImgHistoryInterface::STATUS_START);
        $item->setStartImportDate(date('Y-m-d H:i:s'));

        $this->updateProcess($item, __('Iniciando importación'));

        try {
            $data = $this->readFile($item->getName(), $item->getFile());
            $item->setTotalFiles($data['total']);
            $this->updateProcess($item, __('Total de imagenes identificadas: %1', $data['total']));

            if ($data['total'] > 0) {
                $data = $this->moveImages($data['data']);
                $this->updateProducts($data);
                $ok = true;
            } else {
                $msg = __('No se encontro ninguna imagen');
            }
        } catch (\Exception $e) {
            $this->log->error('Error importing images', ['e' => $e]);
            $msg = __('Ocurrio un error inesperado intentando importar las imagenes');
        }

        if ($ok) {
            $item->setStatus(SouthbayProductImportImgHistoryInterface::STATUS_END);
            $msg = __('Imagenes importadas correctamente');
        } else {
            $item->setStatus(SouthbayProductImportImgHistoryInterface::STATUS_ERROR);
        }

        $item->setEndImportDate(date('Y-m-d H:i:s'));
        $this->updateProcess($item, $msg);
    }

    private function updateProcess(SouthbayProductImportImgHistoryInterface $item, $msg)
    {
        if (!empty($msg)) {
            $item->setResultMsg($msg);
        }
        $this->repository->save($item);
    }

    private function load()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SouthbayProductImportImgHistoryInterface::ENTITY_STATUS, SouthbayProductImportImgHistoryInterface::STATUS_INIT);
        $collection->addOrder(SouthbayProductImportImgHistoryInterface::ENTITY_CREATED_AT, 'ASC');
        $collection->load();

        return $collection->getItems();
    }

    private function updateProducts($data)
    {
        foreach ($data as $sku => $files) {
            $this->productLoader->updateImgBySku($sku, $files);
        }
    }

    private function moveImages($data)
    {
        $result = [];

        foreach ($data as $sku => $files) {
            $_files = [];
            foreach ($files as $file) {

                $baseImage = $this->optimizeImage($file, 'base');
                $smallImage = $this->optimizeImage($file, 'small');
                $thumbnailImage = $this->optimizeImage($file, 'thumbnail');
                $excelImage = $this->optimizeImage($file, 'excel');

                $_files[] = [
                    'base' => $baseImage,
                    'small' => $smallImage,
                    'thumbnail' => $thumbnailImage,
                    'excel' => $excelImage
                ];
            }

            $result[$sku] = $_files;
//            if ($sku === 'AR3565-101') {
//                $this->log->error("Archivos encontrados para SKU {$sku} - \$files: " . print_r($files, true));
//                $this->log->error("Archivos procesados (\$_files) para SKU {$sku}: " . print_r($_files, true));
//                $this->log->error("Estado de \$result para SKU {$sku}: " . print_r($result[$sku], true));
//            }
        }

        return $result;
    }

    private function optimizeImage($file, $type)
    {
        try {
            $filename = basename($file);
            $suffix = '__' . strtoupper($type) . '__';
            $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $newFilename = $filenameWithoutExtension . $suffix . '.' . $extension;

            $targetFile = $this->productLoader->getImgFolder() . '/' . $newFilename;

            if (file_exists($targetFile)) {
                if (!unlink($targetFile)) {
                    throw new \Exception("No se pudo eliminar el archivo existente: {$targetFile}");
                }
            }
            $this->resizeImage($file, $targetFile, $type);

            return $targetFile;
        } catch (\Exception $e) {
            $errorDetails = [
                'File' => $file,
                'Type' => $type,
                'File Exists' => file_exists($file) ? 'Yes' : 'No',
                'File Size' => file_exists($file) ? filesize($file) . ' bytes' : 'N/A',
                'Readable' => is_readable($file) ? 'Yes' : 'No',
                'Writable' => is_writable($file) ? 'Yes' : 'No',
                'Mime Type' => function_exists('mime_content_type') && file_exists($file) ? mime_content_type($file) : 'Unknown',
                'Error Message' => $e->getMessage(),
                'Stack Trace' => $e->getTraceAsString(),
            ];

            $this->log->error("Error en optimizeImage: " . print_r($errorDetails, true));

            return null;
        }

    }


    private function resizeImage($source, $target, $type)
    {

        switch ($type) {
            case 'base':
                $width = 800;
                $height = 800;
                break;
            case 'small':
                $width = 300;
                $height = 300;
                break;
            case 'thumbnail':
                $width = 150;
                $height = 150;
                break;
            case 'excel':
                $width = 50;
                $height = 50;
                break;
            default:
                throw new \InvalidArgumentException("Tipo de imagen no válido: $type");
        }

        $image = $this->imageFactory->create($source);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor([255, 255, 255]);
        $image->resize($width, $height);

        $image->save($target);
//        try {
//            chmod($target, 0755);
//            chown($target, 'www-data');
//            chgrp($target, 'www-data');
//        } catch (\Exception $e) {
//            $this->log->error("Error asignando permisos al archivo $target: " . $e->getMessage());
//        }
        return $target;
    }


    private function readFile($name, $filename)
    {
        $media_folder = SouthbayProductImportImgCronOptimized::UPLOAD_PATH . '/' . $name;
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $target = $mediaDirectory->getAbsolutePath($media_folder);
        $file = $target . '/' . $filename;

        $this->log->debug('Loading file: ' . $file);

        if (file_exists($file)) {
            $cmd = "cd '$target' && unzip '$file' && rm -f '$file'";
            $this->log->debug('Executing cmd:', [$cmd]);
            $output = shell_exec($cmd);
            $this->log->debug('Command output:', [$output]);
        }

        $result = [];
        $total = 0;
        $files = scandir($target);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $target . '/' . $file;


            if (str_contains($file, '__THUMBNAIL__') || str_contains($file, '__SMALL__') || str_contains($file, '__EXCEL__')) {
                if (unlink($filePath)) {
                    $this->log->error('Archivo eliminado correctamente: ' . $filePath);
                } else {
                    $this->log->error('Error al eliminar archivo: ' . $filePath);
                }
                continue;
            }

            if (str_contains($file, '__BASE__')) {
                $file = str_replace('__BASE__', '', $file);
                $newFilePath = $target . DIRECTORY_SEPARATOR . $file;

                if (file_exists($newFilePath)) {
                    $this->log->error("El archivo de destino ya existe, no se renombrará: {$newFilePath}");
                    continue;
                }

                if (rename($filePath, $newFilePath)) {
                    $this->log->error('Archivo renombrado correctamente: ' . $newFilePath);
                    $filePath = $newFilePath;
                } else {
                    $this->log->error("Error al renombrar archivo: {$filePath} -> {$newFilePath}");
                    continue;
                }
            }


            if (is_file($filePath)) {
                if (str_starts_with($file, 'AURORA_')) {
                    $parts = explode('_', $file);
                    if (count($parts) === 3) {
                        $sku = $parts[1];
                        $result[$sku][] = $filePath;
                        $total++;
                    }
                } else {
                    $parts = explode('-', $file);
                    if (count($parts) === 3) {
                        $sku = $parts[0] . '-' . $parts[1];
                        $result[$sku][] = $filePath;
                        $total++;
                    }
                }
            } elseif (is_dir($filePath)) {
                $subFiles = scandir($filePath);
                foreach ($subFiles as $subFile) {
                    if ($subFile === '.' || $subFile === '..') {
                        continue;
                    }
                    $subFilePath = $filePath . '/' . $subFile;
                    // Manejar archivos de imagen en subcarpetas
                    if (is_file($subFilePath)) {
                        if (str_starts_with($subFile, 'AURORA_')) {
                            $parts = explode('_', $subFile);
                            if (count($parts) === 3) {
                                $sku = $parts[1];
                                $result[$sku][] = $subFilePath;
                                $total++;
                            }
                        } else {
                            $parts = explode('-', $subFile);
                            if (count($parts) === 3) {
                                $sku = $parts[0] . '-' . $parts[1];
                                $result[$sku][] = $subFilePath;
                                $total++;
                            }
                        }
                    }
                }
            }
        }

        return ['total' => $total, 'data' => $result];
    }


    public function optimizeImportedImages()
    {
        try {
            $data = $this->findImagesInExistingFolders();
            if ($data['total'] > 0) {
                $data = $this->moveImages($data['data']);
                $this->updateProducts($data);
                SouthbayProductImportCron::cacheClean($this->log, 'full_page');
            } else {
                $this->log->error('No se encontro ninguna imagen');
            }
        } catch (\Exception $e) {
            $this->log->error('Error reprocessing images', ['e' => $e]);
        }


    }

    private function findImagesInExistingFolders()
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $baseFolder = $mediaDirectory->getAbsolutePath('catalog/product');

        $result = [];
        $total = 0;

        if (!is_dir($baseFolder)) {
            $this->log->error('El directorio base no existe: ' . $baseFolder);
            return ['total' => 0, 'data' => []];
        }


        $directoryIterator = new \RecursiveDirectoryIterator($baseFolder, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $fileInfo) {
            $filePath = $fileInfo->getRealPath();
            if ($fileInfo->isDir()) {
                if (basename($filePath) === 'cache') {
                    $iterator->next();
                }
            } else {
                $filePath = $fileInfo->getRealPath();
                $fileName = $fileInfo->getFilename();


                if (str_contains($fileName, '__THUMBNAIL__') || str_contains($fileName, '__SMALL__') || str_contains($fileName, '__EXCEL__')) {
                    //          $this->log->error('Eliminando archivo: ' . $filePath);
                    if (unlink($filePath)) {
                        //               $this->log->error('Archivo eliminado correctamente: ' . $filePath);
                    } else {
                        $this->log->error('Error al eliminar archivo: ' . $filePath);
                    }
                    continue;
                }
                if (str_contains($fileName, '__BASE__')) {
                    $newFileName = str_replace('__BASE__', '', $fileName);
                    $newFilePath = $fileInfo->getPath() . DIRECTORY_SEPARATOR . $newFileName;

                    if (file_exists($newFilePath)) {
                        //               $this->log->error("El archivo de destino ya existe, no se renombrará: {$newFilePath}");
                        continue;
                    }

                    //           $this->log->error("Renombrando archivo: {$filePath} -> {$newFilePath}");
                    if (rename($filePath, $newFilePath)) {
                        //                 $this->log->error('Archivo renombrado correctamente: ' . $newFilePath);
                        $filePath = $newFilePath;
                    } else {
                        $this->log->error("Error al renombrar archivo: {$filePath} -> {$newFilePath}");
                        continue;
                    }
                }

                if (is_file($filePath)) {
                    if (str_starts_with($fileName, 'AURORA_')) {
                        $parts = explode('_', $fileName);
                        if (count($parts) === 3) {
                            $sku = $parts[1];
                            if (!isset($result[$sku])) {
                                $result[$sku] = [];
                            }
                            $result[$sku][] = $filePath;
                            $total++;
                            //                    $this->log->error("Archivo válido para SKU AURORA: {$sku}");
                        }
                    } else {
                        $parts = explode('-', $fileName);
                        if (count($parts) === 3) {
                            $sku = $parts[0] . '-' . $parts[1];
                            if (!isset($result[$sku])) {
                                $result[$sku] = [];
                            }
                            $result[$sku][] = $filePath;
                            $total++;
                            //                  $this->log->error("Archivo válido para SKU: {$sku}");
                        }
                    }
                }
            }
        }

        $this->log->error("Total archivos procesados: {$total}");
        return ['total' => $total, 'data' => $result];
    }


}
