<?php

namespace Southbay\ApproveOrders\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Psr\Log\LoggerInterface;
use Southbay\Product\Cron\SouthbayProductImportImgCronOptimized;
use Southbay\Product\Cron\SouthbayProductImportImgCron;
use Southbay\Product\Cron\SouthbayProductImportCron;
use Southbay\Product\Model\StockAtp;
use Southbay\Product\Model\CleanDuplicatesImagesFolder;

class RunScript extends Command
{
    private $logger;
    private $importImgCronOptimized;
    private $importImgCron;
    private $productImportCron;
    private $stockAtp;
    private $cleanDuplicatesImagesFolder;

    public function __construct(
        LoggerInterface $logger,
        SouthbayProductImportImgCronOptimized $importImgCronOptimized,
        SouthbayProductImportImgCron $importImgCron,
        SouthbayProductImportCron $productImportCron,
        StockAtp $stockAtp,
        CleanDuplicatesImagesFolder $cleanDuplicatesImagesFolder
    ) {
        $this->logger = $logger;
        $this->importImgCronOptimized = $importImgCronOptimized;
        $this->importImgCron = $importImgCron;
        $this->productImportCron = $productImportCron;
        $this->stockAtp = $stockAtp;
        $this->cleanDuplicatesImagesFolder = $cleanDuplicatesImagesFolder;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:run-script')
            ->setDescription('Run various processes related to Southbay module interactively');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $processes = [
            'optimized' => 'Optimizar imagenes cargadas (importImgCronOptimized->optimizeImportedImages)',
            'import'    => 'Correr subida de imágenes (importImgCronOptimized->run)',
            'product'   => 'Correr subida de productos (productImportCron->run)',
            'stock_atp' => 'Correr actualización de stock (stockAtp->updateStock())',
            'clean_images' => 'Limpiar imágenes duplicadas en "fotos" (cleanDuplicatesImagesFolder->findImagesInExistingFolders)',
        ];

        $question = new ChoiceQuestion(
            '<question>Please select a process to execute:</question>',
            array_values($processes)
        );
        $question->setErrorMessage('Invalid selection.');

        $selectedProcess = $helper->ask($input, $output, $question);
        $processKey = array_search($selectedProcess, $processes);

        if (!$processKey) {
            $output->writeln('<error>Invalid process selection.</error>');
            return Cli::RETURN_FAILURE;
        }

        try {
            switch ($processKey) {
                case 'optimized':
                    $output->writeln('<info>Running optimized image import...</info>');
                    $this->importImgCronOptimized->optimizeImportedImages();
                    break;

                case 'import':
                    $output->writeln('<info>Running image import...</info>');
                    $this->importImgCronOptimized->run();
                    break;

                case 'product':
                    $output->writeln('<info>Running product import...</info>');
                    $this->productImportCron->run();
                    break;

                case 'stock_atp':
                    $output->writeln('<info>Running stock ATP update...</info>');
                    $this->stockAtp->updateStock();
                    break;

                case 'clean_images':
                    $output->writeln('<info>Cleaning duplicate images...</info>');
                    $total = $this->cleanDuplicatesImagesFolder->findImagesInExistingFolders();
                    $output->writeln("<info>Total files processed: {$total}</info>");
                    break;
            }

            $output->writeln('<info>Process executed successfully.</info>');
            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $this->logger->error('Error executing process: ' . $e->getMessage());
            $output->writeln('<error>Error executing process: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}
