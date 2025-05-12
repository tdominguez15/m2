<?php

namespace Southbay\ApproveOrders\Controller\Run;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use Southbay\Product\Cron\SouthbayProductImportImgCronOptimized;
use Southbay\Product\Cron\SouthbayProductImportImgCron;
use Southbay\Product\Cron\SouthbayProductImportCron;



class ImportImages implements HttpGetActionInterface
{
    /**
     * @var SouthbayProductImportImgCronOptimized
     */
    protected $importImgCronOptimized;

    /**
     * @var SouthbayProductImportImgCron
     */
    protected $importImgCron;

    /**
     * @var SouthbayProductImportCron
     */
    protected $productImportCron;


    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;




    /**
     * Constructor
     *
     * @param SouthbayProductImportImgCronOptimized $importImgCronOptimized
     * @param SouthbayProductImportImgCron $importImgCron
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param SouthbayProductImportCron $productImportCron
     */
    public function __construct(

        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        SouthbayProductImportImgCronOptimized $importImgCronOptimized,
        SouthbayProductImportImgCron $importImgCron,
        SouthbayProductImportCron $productImportCron,

    ) {

        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->importImgCron = $importImgCron;
        $this->productImportCron = $productImportCron;
        $this->importImgCronOptimized = $importImgCronOptimized;

    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {

        //    $this->importImgCronOptimized->run();
            $this->importImgCronOptimized->optimizeImportedImages();
        //    $this->productImportCron->run();
        //    $this->importImgCron->run();
            $result = ['success' => true, 'message' => 'Cron job executed successfully'];
        } catch (\Exception $e) {
            $this->logger->error('Error executing cron job: ' . $e->getMessage());
            $result = ['success' => false, 'message' => 'Error executing cron job'];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}

