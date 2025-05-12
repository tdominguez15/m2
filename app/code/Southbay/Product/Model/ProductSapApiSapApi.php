<?php

namespace Southbay\Product\Model;

use Psr\Log\LoggerInterface;
use Southbay\Product\Api\ProductSapApiInterface;
use Southbay\Product\Api\Response\ProductInterfaceResponse;
use Southbay\Product\Model\Response\ProductApiInterfaceResponse;

class ProductSapApiSapApi implements ProductSapApiInterface
{
    protected $productSapInterfaceFactory;
    protected $productSapResource;

    private $log;

    public function __construct(
        \Southbay\Product\Model\ProductSapInterfaceFactory        $productSapInterfaceFactory,
        \Southbay\Product\Model\ResourceModel\ProductSapInterface $productSapResource,
        LoggerInterface                                           $log
    )
    {
        $this->productSapInterfaceFactory = $productSapInterfaceFactory;
        $this->productSapResource = $productSapResource;
        $this->log = $log;
    }

    /**
     * @param mixed $ET_ART_PRC
     * @return ProductInterfaceResponse
     */
    public function save($ET_ART_PRC): ProductInterfaceResponse
    {
        try {
            /**
             * @var \Southbay\Product\Model\ProductSapInterface $productSap
             */
            $productSap = $this->productSapInterfaceFactory->create();
            $productSap->setStatus('ok');
            $productSap->setResultMsg(__('Pendiente de incorporar'));
            $productSap->setRawData(json_encode($ET_ART_PRC));

            $this->productSapResource->save($productSap);

            return new ProductApiInterfaceResponse('success', 'Product saved successfully');
        } catch (\Exception $e) {
            $this->log->error('Error processing request: ', ['error' => $e]);
            return new ProductApiInterfaceResponse('error', 'Unexpected error ocurred processing the request');
        }
    }
}
