<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

class SouthbayConfigNotificationRtvRepository
{
    private $log;
    private $collectionFactory;
    private $repository;
    private $factory;

    private $send_email_notification_helper;

    public function __construct(
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayConfigNotificationRtvCollectionFactory $collectionFactory,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtv                             $repository,
        \Southbay\ReturnProduct\Model\SouthbayConfigNotificationRtvFactory                                    $factory,
        \Southbay\ReturnProduct\Helper\SendEmailNotification                                                  $send_email_notification_helper,
        \Psr\Log\LoggerInterface                                                                              $log
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->send_email_notification_helper = $send_email_notification_helper;
        $this->log = $log;
    }

    /**
     * @param $data
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv
     */
    public function findOrNew($data)
    {
        if (empty($data)) {
            return $this->factory->create();
        } else {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv::ENTITY_COUNTRY_CODE, $data['country']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv::ENTITY_TYPE, $data['type']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv::ENTITY_TEMPLATE_CODE, $data['template']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv::ENTITY_STATUS, $data['status']);

            return $collection->getFirstItem();
        }
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv $model
     */
    public function save($model)
    {
        $this->repository->save($model);

        return $model;
    }

    public function sendNotification(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model, $country_code, $force_send_reject_notification = false)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv::ENTITY_COUNTRY_CODE, $country_code);

        if ($force_send_reject_notification) {
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv::ENTITY_STATUS, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_REJECTED_IN_CONTROL_QA);
        } else {
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv::ENTITY_STATUS, $model->getStatus());
        }

        $collection->load();
        $items = $collection->getItems();

        if (empty($items)) {
            return;
        }

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv $item
         */
        foreach ($items as $item) {
            $this->send_email_notification_helper->sendCustomerNotification($item->getTemplateCode(), $model);
        }
    }
}
