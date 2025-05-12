<?php

namespace Southbay\ReturnProduct\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;

abstract class Base implements ActionInterface
{
    protected $context;
    protected $_messageManager;

    public function __construct(Context                                     $context,
                                \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->context = $context;
        $this->_messageManager = $messageManager;
    }

    public function execute()
    {
        $_view = $this->context->getView();
        $_view->loadLayout();
        $_view->renderLayout();
        return $this->context->getResponse();
    }
}
