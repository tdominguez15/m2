<?php

namespace Southbay\CustomCheckout\Controller\Purchase;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Southbay\CustomCheckout\Helper\Data as Southbay_CustomCheckout_Helper;

class NextPoNumber implements HttpGetActionInterface
{
    protected $_log;

    protected $_factory;

    protected $_helper;

    public function __construct(

        \Psr\Log\LoggerInterface       $log,
        Southbay_CustomCheckout_Helper $helper,
        JsonFactory                    $factory
    )
    {
        $this->_log = $log;
        $this->_factory = $factory;
        $this->_helper = $helper;
    }

    /**
     * Company List page.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->_factory->create();
        $result->setData(['new_po_number' => $this->_helper->getPoNumber()]);

        return $result;
    }
}
