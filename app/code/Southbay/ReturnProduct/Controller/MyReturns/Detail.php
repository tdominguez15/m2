<?php

namespace Southbay\ReturnProduct\Controller\MyReturns;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Detail implements HttpGetActionInterface
{
    private $log;
    private $context;
    private $resultPageFactory;

    public function __construct(Context                  $context,
                                PageFactory              $resultPageFactory,
                                \Psr\Log\LoggerInterface $log)
    {
        $this->context = $context;
        $this->log = $log;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
