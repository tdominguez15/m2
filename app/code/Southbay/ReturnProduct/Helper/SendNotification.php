<?php

namespace Southbay\ReturnProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class SendNotification extends AbstractHelper
{
    private $context;
    private $configNotificationRtvRepository;
    private $configNotificationRtvByRolRepository;

    public function __construct(Context                                                                                  $context,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtvRepository      $configNotificationRtvRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtvByRolRepository $configNotificationRtvByRolRepository)
    {
        $this->configNotificationRtvRepository = $configNotificationRtvRepository;
        $this->configNotificationRtvByRolRepository = $configNotificationRtvByRolRepository;
        parent::__construct($context);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @param string $country_code
     * @param bool $ready_for_approval
     * @param bool $force_send_reject_notification
     * @return void
     */
    public function send($model, $country_code, $ready_for_approval, $force_send_reject_notification = false)
    {
        try {
            $this->sendToCustomer($model, $country_code, $force_send_reject_notification);
        } catch (\Exception $e) {
            $this->_logger->error('Error trying to send customer notification', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        try {
            $this->sendToAdmin($model, $country_code, $ready_for_approval);
        } catch (\Exception $e) {
            $this->_logger->error('Error trying to send admin notification', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    private function sendToAdmin($model, $country_code, $ready_for_approval)
    {
        $this->_logger->debug('sendToAdmin. trying...', ['id' => $model->getId()]);
        $this->configNotificationRtvByRolRepository->sendNotification($model, $country_code, $ready_for_approval);
    }

    private function sendToCustomer($model, $country_code, $force_send_reject_notification = false)
    {
        $this->configNotificationRtvRepository->sendNotification($model, $country_code, $force_send_reject_notification);
    }
}
