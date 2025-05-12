<?php

namespace Southbay\CustomCheckout\Plugin\Checkout\Validation;

use Magento\Framework\Controller\AbstractResult;
use Southbay\CustomCheckout\Helper\Data as Southbay_CustomCheckout_Helper;

class ValidateItems
{
    private $log;
    private $helper;
    private $resultRedirectFactory;

    private $messageManager;

    public function __construct(Southbay_CustomCheckout_Helper                       $helper,
                                \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
                                \Magento\Framework\Message\ManagerInterface          $messageManager,
                                \Psr\Log\LoggerInterface                             $log)
    {
        $this->log = $log;
        $this->helper = $helper;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    public function afterExecute(\Magento\Checkout\Controller\Index\Index $subject, AbstractResult $result)
    {
        $this->log->info('ValidateItems......................', ['result' => $result]);

        if ($result instanceof \Magento\Framework\Controller\Result\Redirect) {
            return $result;
        }

        $items = $this->helper->getQuoteItems();

        if (!empty($items['invalid'])) {
            $this->log->info('Hay productos sin cantidades');
            $message = __('Tiene productos sin cantidades');
            $this->messageManager->addWarningMessage($message);

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');

            $result = $resultRedirect;
        }

        return $result;
    }
}
