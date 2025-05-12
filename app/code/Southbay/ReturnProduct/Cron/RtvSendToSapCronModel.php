<?php

namespace Southbay\ReturnProduct\Cron;

use Southbay\ReturnProduct\Helper\SendSapRtvRequest;

class RtvSendToSapCronModel
{
    private $log;
    private $sapRtvRequest;

    private $returnProductRepository;

    private $sapInterfaceRepository;

    public function __construct(\Psr\Log\LoggerInterface                                                    $log,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $returnProductRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterface            $sapInterfaceRepository,
                                SendSapRtvRequest                                                           $sapRtvRequest)
    {
        $this->log = $log;
        $this->sapRtvRequest = $sapRtvRequest;
        $this->returnProductRepository = $returnProductRepository;
        $this->sapInterfaceRepository = $sapInterfaceRepository;
    }

    public function execute()
    {
        $items = $this->sapRtvRequest->getRtvPendingToSend();
        $groups = [];

        foreach ($items as $item) {
            if (!isset($groups[$item->getRef()])) {
                $groups[$item->getRef()] = [];
            }

            $groups[$item->getRef()][] = $item;
        }

        foreach ($groups as $id => $items) {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
             */
            $model = $this->returnProductRepository->findById($id);

            if (is_null($model)) {
                $this->log->error('Return product not found', ['return_product_id' => $id]);
                foreach ($items as $item) {
                    $this->sapRtvRequest->updateRequestStatus(
                        $item,
                        __('No se encontro el tramite %1', $id),
                        \Southbay\ReturnProduct\Model\SouthbaySapInterface::STATUS_ERROR
                    );
                }
            } else {
                $this->log->info('Sending return product', ['return_product_id' => $id]);
                foreach ($items as $item) {
                    $this->log->info('** Sending return product', ['return_product_id' => $id, 'sap_doc_id' => $item->getId()]);
                    $result = $this->sapRtvRequest->sendSapRequest($item->getUrl(), [['model' => $item]]);
                    $this->log->info('** End send return product', ['return_product_id' => $id, 'sap_doc_id' => $item->getId(), 'result' => $result]);
                    if ($result) {
                        $this->log->info('All documents sent successfully for return product', ['return_product_id' => $id]);
                        $this->returnProductRepository->markAsDocumentsSent($model);
                    }
                    else {
                        $this->log->error('Failed to send the return product.', ['return_product_id' => $id]);
                    }
                }
                $this->log->info('End sending return product', ['return_product_id' => $id]);
            }
        }
    }
}
