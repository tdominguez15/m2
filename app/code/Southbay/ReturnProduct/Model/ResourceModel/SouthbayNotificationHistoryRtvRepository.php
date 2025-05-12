<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

class SouthbayNotificationHistoryRtvRepository
{
    private $log;
    private $collectionFactory;
    private $repository;
    private $factory;

    public function __construct(
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayNotificationHistoryRtvCollectionFactory $collectionFactory,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayNotificationHistoryRtv                             $repository,
        \Southbay\ReturnProduct\Model\SouthbayNotificationHistoryRtvFactory                                    $factory,
        \Psr\Log\LoggerInterface                                                                               $log
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->log = $log;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getNotificationsCollection()
    {
        return $this->collectionFactory->create();
    }

    public function addNewNotification($data)
    {
        $this->log->debug('ddd', ['d' => $data]);

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayNotificationHistoryRtv $model
         */
        $model = $this->factory->create();
        $model->setCountryCode($data['country_code']);
        $model->setReturnId($data['return_id']);
        $model->setCustomerCode($data['customer_code']);
        $model->setType($data['type']);
        $model->setStatus($data['status']);
        $model->setTemplateCode($data['template_code']);
        $model->setSubject($data['subject']);
        $model->setContent($data['content']);
        $model->setFrom($data['from']);
        $model->setTo($data['to']);

        $this->repository->save($model);
    }
}
