<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SapOrderConfirmationApiResponseInterface;
use Southbay\ReturnProduct\Api\SapOrderConfirmationApiInterface;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocInterface as ResourceModel;
use Southbay\ReturnProduct\Model\SouthbaySapDocInterfaceFactory as ModelFactory;

class SapOrderConfirmation implements SapOrderConfirmationApiInterface
{
    private $log;
    private $repository;
    private $factory;

    public function __construct(\Psr\Log\LoggerInterface $log,
                                ResourceModel            $repository,
                                ModelFactory             $factory)
    {
        $this->log = $log;
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * @param mixed $rows
     * @return SapOrderConfirmationApiResponseInterface
     */
    public function save($rows): SapOrderConfirmationApiResponseInterface
    {
        $result = new SapOrderConfirmationApiResult();

        try {
            // $this->log->debug('SapOrderConfirmation. Start get:', ['rows' => $rows]);

            /**
             * @var \Southbay\ReturnProduct\Model\SouthbaySapDocInterface $model
             */
            $model = $this->factory->create();
            $model->setRawData(json_encode($rows));
            $model->setType('order_entry');
            $model->setStatus('ok');
            $model->setResultMsg('ok');
            $this->repository->save($model);

            $status = 'ok';
            $msg = 'Datos incorporados correctamente';
        } catch (\Exception $e) {
            $this->log->error('SapOrderConfirmation. Error sap doc interface', ['error' => $e]);
            $status = 'error';
            $msg = 'Unexpected error';
        }

        $result->setEstado($status);
        $result->setCodigo($status);
        $result->setDetalle($msg);
        $result->setReferencia1('');
        $result->setReferencia2('');
        $result->setReferencia3('');

        $response = new SapOrderConfirmationApiResponse();
        $response->setReturn($result);
        return $response;
    }
}
