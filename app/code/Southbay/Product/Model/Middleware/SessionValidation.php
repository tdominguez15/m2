<?php

namespace Southbay\Product\Model\Middleware;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Response\RedirectInterface;

class SessionValidation implements ObserverInterface
{
    private $customerSession;
    private $redirect;
    private $log;
    private $responseFactory;

    public function __construct(CustomerSession                        $customerSession,
                                \Magento\Framework\App\ResponseFactory $responseFactory,
                                \Psr\Log\LoggerInterface               $log,
                                RedirectInterface                      $redirect)
    {
        $this->customerSession = $customerSession;
        $this->responseFactory = $responseFactory;
        $this->redirect = $redirect;
        $this->log = $log;
    }

    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getData('request');
        $url = $request->getRequestUri();
        $url = str_replace('/index.php', '', $url);

        if ($url !== '/admin' && !str_starts_with($url, '/admin/')) {
            if (!$this->customerSession->isLoggedIn()
                && !str_contains($url, '/customer/account')
                && !str_contains($url, '/loginascustomer/login')
            ) {
                $response = $this->responseFactory->create();
                $this->redirect->redirect($response, 'customer/account/login');
                $response->sendResponse();
                die();
            }
        }
    }
}
