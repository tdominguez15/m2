<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Api\Data\SapDocApiResponseInterface;
use Southbay\ReturnProduct\Api\Data\SapDocApiResultInterface;
use Southbay\ReturnProduct\Model\SouthbaySapDocInterfaceFactory as ModelFactory;
use Southbay\ReturnProduct\Api\SapDocApiInterface as ApiInterface;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapDocInterface as ResourceModel;

class SapDocApiInterface implements ApiInterface
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
     * @param $rows
     * @return SapDocApiResponseInterface
     */
    public function save($rows): SapDocApiResponseInterface
    {
        try {
            $this->log->debug('SapDocApiInterface. Start get:', ['rows' => $rows]);

            /**
             * @var \Southbay\ReturnProduct\Model\SouthbaySapDocInterface $model
             */
            $model = $this->factory->create();
            $model->setRawData(json_encode($rows));
            $model->setType('rtv');
            $model->setStatus('ok');
            $model->setResultMsg('ok');
            $this->repository->save($model);

            $status = 'ok';
            $msg = 'ok';
        } catch (\Exception $e) {
            $this->log->error('SapDocApiInterface. Error sap doc interface', ['error' => $e]);
            $status = 'error';
            $msg = 'Unexpected error';
        }

        $response = new SapDocApiResponse();

        $return = new SapDocApiResult();
        $return->setMensaje($msg);
        $return->setEstado($status);

        $response->setReturn($return);

        return $response;
    }
}
