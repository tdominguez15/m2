<?php

namespace Southbay\Product\Cron;

use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\Product\Api\Data\SouthbayProductImportImgHistoryInterface;
use Southbay\Product\Model\Import\ProductLoader;
use Southbay\Product\Plugin\Magento\Indexer\CheckCustomIndexerConfig;

class SouthbayProductImportImgCron
{
    const UPLOAD_PATH = 'product/import/img';

    private $log;
    private $collectionFactory;

    private $repository;

    private $filesystem;

    private $scopeConfig;

    private $storeManager;

    private $productLoader;

    private $checkCustomIndexerConfig;

    public function __construct(\Psr\Log\LoggerInterface                                                                $log,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory\CollectionFactory $collectionFactory,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory                   $repository,
                                Filesystem                                                                              $filesystem,
                                \Magento\Framework\App\Config\ScopeConfigInterface                                      $scopeConfig,
                                StoreManagerInterface                                                                   $storeManager,
                                ProductLoader                                                                           $productLoader,
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
            $msg = __('Ocurrio un error inesperado intentando importar los productos');
        }

        if ($ok) {
            $item->setStatus(SouthbayProductImportImgHistoryInterface::STATUS_END);
            $msg = __('Producto importado correctamente');
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

        $target = $this->productLoader->getImgFolder();

        foreach ($data as $sku => $files) {
            $_files = [];
            foreach ($files as $file) {
                $_file = $target . '/' . basename($file);
                if (file_exists($_file)) {
                    unlink($_file);
                }

                link($file, $_file);

                $_files[] = $_file;
            }

            $result[$sku] = $_files;
        }

        return $result;
    }

    private function readFile($name, $filename)
    {
        $media_folder = SouthbayProductImportImgCron::UPLOAD_PATH . '/' . $name;
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $target = $mediaDirectory->getAbsolutePath($media_folder);
        $file = $target . '/' . $filename;


        $this->log->debug('Loading file: ' . $file);

        if (file_exists($file)) {
            $cmd = "cd '$target' && unzip '$file' && rm -f '$file'";
            $this->log->debug('Executing cmd:', [$cmd]);
            shell_exec($cmd);
        }

        $result = [];

        $total = 0;
        $files = scandir($target);

        foreach ($files as $image) {
            if ($image === '.' || $image === '..') {
                continue;
            }

            if (str_starts_with($image, 'AURORA_')) {
                $parts = explode('_', $image);
                if (count($parts) === 3) {
                    $sku = $parts[1];
                    if (!isset($result[$sku])) {
                        $result[$sku] = [];
                    }

                    $total++;
                    $result[$sku][] = $target . '/' . $image;
                }
            } else {
                $parts = explode('-', $image);
                if (count($parts) === 3) {
                    $sku = $parts[0] . '-' . $parts[1];

                    if (!isset($result[$sku])) {
                        $result[$sku] = [];
                    }

                    $total++;
                    $result[$sku][] = $target . '/' . $image;
                }
            }
        }

        return ['total' => $total, 'data' => $result];
    }
}
