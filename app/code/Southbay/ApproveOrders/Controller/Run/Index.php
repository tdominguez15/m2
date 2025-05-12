<?php

namespace Southbay\ApproveOrders\Controller\Run;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Southbay\ApproveOrders\Cron\CancelOrderAtonce;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Southbay\CustomCustomer\Cron\AtOnceNotifications;
use Southbay\Product\Model\StockAtp;

class Index implements HttpGetActionInterface
{
    /**
     * @var CancelOrderAtonce
     */
    protected $cancelOrderAtonce;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var AtOnceNotifications
     */
    protected $atOnceNotifications;

    /**
     * @var StockAtp
     */
    protected $stockAtp;


    /**
     * Constructor
     *
     * @param CancelOrderAtonce $cancelOrderAtonce
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param AtOnceNotifications $atOnceNotifications
     * @param StockAtp $stockAtp
     */
    public function __construct(

        CancelOrderAtonce $cancelOrderAtonce,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        AtOnceNotifications $atOnceNotifications,
        StockAtp $stockAtp

    ) {
        $this->cancelOrderAtonce = $cancelOrderAtonce;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->atOnceNotifications = $atOnceNotifications;
        $this->stockAtp = $stockAtp;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        try {
        //    die("desactivado");
            $this->stockAtp->updateStock();
       //     $this->atOnceNotifications->execute();
       //     $this->cancelOrderAtonce->execute();
            $result = ['success' => true, 'message' => 'Cron job executed successfully'];
        } catch (\Exception $e) {
            $this->logger->error('Error executing cron job: ' . $e->getMessage());
            $result = ['success' => false, 'message' => 'Error executing cron job'];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}

