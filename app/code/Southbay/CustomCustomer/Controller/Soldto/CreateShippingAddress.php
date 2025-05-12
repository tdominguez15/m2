<?php

namespace Southbay\CustomCustomer\Controller\Soldto;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Southbay\CustomCustomer\ViewModel\SoldToViewModel;
use Magento\Framework\Controller\Result\JsonFactory;

class CreateShippingAddress extends Action
{
    protected $soldToViewModel;
    protected $resultJsonFactory;
    private $session;
    private $cache_manager;

    private $log;

    public function __construct(
        Context                              $context,
        SoldToViewModel                      $soldToViewModel,
        JsonFactory                          $resultJsonFactory,
        \Magento\Customer\Model\Session      $session,
        \Psr\Log\LoggerInterface             $log,
        \Magento\Framework\App\Cache\Manager $cache_manager
    )
    {
        parent::__construct($context);
        $this->soldToViewModel = $soldToViewModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->log = $log;
        $this->cache_manager = $cache_manager;
    }

    public function execute()
    {
        $result = ['success' => false];

        $soldToId = $this->getRequest()->getParam('soldToId');

        if ($soldToId) {
            try {
                $this->session->start();
                if ($this->session->getSoldtoId() && $this->session->getSoldtoId() != $soldToId) {
                    $this->log->debug('flush cache');
                    $this->cache_manager->flush(['full_page']);
                }
                $this->session->setSoldtoId($soldToId);
                $this->log->debug('session id', ['sold_to_id' => $this->session->getSoldtoId()]);
                $result['success'] = $this->soldToViewModel->createShippingAddressFromData($soldToId);
            } catch (\Exception $e) {
                $result['error_message'] = $e->getMessage();
            }
        } else {
            $result['error_message'] = 'No se proporcionó el ID de SoldTo.';
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
