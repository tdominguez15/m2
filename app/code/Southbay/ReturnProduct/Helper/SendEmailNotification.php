<?php

namespace Southbay\ReturnProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class SendEmailNotification extends AbstractHelper
{
    private $customerRepository;
    private $transportBuilder;
    private $inlineTranslation;
    private $storeManager;
    private $notificationHistoryRtvRepository;
    private $state;

    public function __construct(Context                                                                              $context,
                                TransportBuilder                                                                     $transportBuilder,
                                StateInterface                                                                       $inlineTranslation,
                                StoreManagerInterface                                                                $storeManager,
                                State                                                                                $state,
                                \Magento\Customer\Model\ResourceModel\CustomerRepository                             $customerRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayNotificationHistoryRtvRepository $notificationHistoryRtvRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->notificationHistoryRtvRepository = $notificationHistoryRtvRepository;
        $this->state = $state;
        parent::__construct($context);
    }

    public function sendCustomerNotification($template_code,
                                             \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model)
    {
        $customer = $this->customerRepository->getById($model->getUserCode());
        $customer_name = $customer->getLastname() . ' ' . $customer->getFirstname();
        $this->sendNotification($template_code, $customer->getEmail(), $customer_name, $model);
    }

    public function sendNotification(
        $template_code,
        $to_email,
        $to_name,
        \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model,
        $extra_vars = [])
    {
        $templateVars = [
            'id' => $model->getId(),
            'customer_code' => $model->getCustomerCode(),
            'customer_name' => $model->getCustomerName(),
            'status' => $model->getStatusName(),
            'total_qty' => $model->getTotalReturn(),
            'total_amount' => $model->getTotalAmount()
        ];

        if (!empty($extra_vars)) {
            $templateVars = array_merge($templateVars, $extra_vars);
        }

        $result = $this->sendAnyNotification($template_code, $to_email, $to_name, $templateVars);

        $message = $result['message'];
        $email = $result['from'];

        $this->notificationHistoryRtvRepository->addNewNotification([
            'country_code' => $model->getCountryCode(),
            'return_id' => $model->getId(),
            'customer_code' => $model->getCustomerCode(),
            'type' => $model->getType(),
            'status' => $model->getStatus(),
            'template_code' => $template_code,
            'subject' => ($result['sent'] ? $message->getSubject() : $message),
            'content' => '',
            'from' => $email,
            'to' => $to_email
        ]);
    }

    public function sendAnyNotification(
        $template_code,
        $to_email,
        $to_name,
        $templateVars = [],
        $store_id = null)
    {
        if (is_null($store_id)) {
            $store = $this->storeManager->getStore(true);
            $store_id = $store->getId();
        }

        $email = $this->scopeConfig->getValue('system/gmailsmtpapp/custom_from_email');
        if (empty($email)) {
            $email = $this->scopeConfig->getValue('system/gmailsmtpapp/username');
        }

        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
        }

        // Load email template
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($template_code)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store_id])
            ->setTemplateVars($templateVars)
            ->setFromByScope(['name' => '', 'email' => $email])
            ->addTo($to_email, $to_name)
            ->getTransport();

        $this->_logger->debug('Trying to send customer email notification', ['to' => $to_email]);
        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_logger->error('Error trying to send customer email notification', ['e' => $e]);
            $this->inlineTranslation->resume();
            return ['sent' => false, 'from' => $email, 'message' => $e->getMessage()];
        }

        $this->inlineTranslation->resume();

        return [
            'sent' => true,
            'from' => $email,
            'message' => $transport->getMessage()
        ];
    }
}
