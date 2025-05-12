<?php

namespace Southbay\Product\Model;

use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

class CleanDuplicatesImagesFolder
{
    private $filesystem;
    private $log;

    public function __construct(
        Filesystem      $filesystem,
        LoggerInterface $log
    ) {
        $this->filesystem = $filesystem;
        $this->log = $log;
    }

    /**
     * Encuentra y limpia imágenes duplicadas en la carpeta de productos
     *
     * @return int Total de archivos procesados
     */
    public function findImagesInExistingFolders(): int
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $baseFolder = $mediaDirectory->getAbsolutePath('fotos');

        if (!is_dir($baseFolder)) {
            $this->log->error('El directorio base no existe: ' . $baseFolder);
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseFolder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $total = 0;

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $filePath = $fileInfo->getRealPath();
            $fileName = $fileInfo->getFilename();
            $total++;

            // 🗑️ Eliminar archivos innecesarios
            if (preg_match('/__(THUMBNAIL|SMALL|EXCEL)__/i', $fileName)) {
                if (@unlink($filePath)) {
                    $this->log->info('Archivo eliminado: ' . $filePath);
                } else {
                    $this->log->error('Error al eliminar archivo: ' . $filePath);
                }
                continue;
            }

            //  Renombrar imágenes base y establecer permisos
            if (str_contains($fileName, '__BASE__')) {
                $newFilePath = str_replace('__BASE__', '', $filePath);
                if (@rename($filePath, $newFilePath)) {
                    $this->log->info('Archivo renombrado: ' . $newFilePath);

                    //  **Establecer permisos al archivo**
                    if (@chmod($newFilePath, 0755)) {
                        $this->log->info('Permisos asignados: 0755 a ' . $newFilePath);
                    } else {
                        $this->log->error('Error al asignar permisos a ' . $newFilePath);
                    }
                } else {
                    $this->log->error("Error al renombrar archivo: {$filePath}");
                }
            }
        }

        $this->log->info("Total archivos procesados: {$total}");
        return $total;
    }
}
