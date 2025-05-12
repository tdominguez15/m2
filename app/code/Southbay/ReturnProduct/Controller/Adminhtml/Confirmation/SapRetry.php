<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Confirmation;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Southbay\ReturnProduct\Helper\SendSapRtvRequest;

class SapRetry extends \Magento\Backend\App\Action
{
    private $_resultFactory;
    private $log;
    private $_context;
    private $sapRtvRequestHelper;

    public function __construct(Context                  $context,
                                JsonFactory              $resultFactory,
                                SendSapRtvRequest        $sapRtvRequestHelper,
                                \Psr\Log\LoggerInterface $log
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_resultFactory = $resultFactory;
        $this->sapRtvRequestHelper = $sapRtvRequestHelper;
        $this->log = $log;
    }

    public function execute()
    {
        $params = $this->_context->getRequest()->getParams();

        $this->log->debug('Sap retry params', ['params' => $params]);

        if (isset($params['id'])) {
            $model = $this->sapRtvRequestHelper->findSapRequest($params['id']);
            if (!is_null($model)) {
                $this->sapRtvRequestHelper->retry($model);
            }
        }

        $result = $this->_resultFactory->create();
        $result->setData([]);

        return $result;
    }
}
