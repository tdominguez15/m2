<?php

namespace Southbay\CustomCheckout\Controller\Cart;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;

class Clean implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_context;

    protected $_log;

    protected $_session;

    protected $_checkoutCart;

    protected $_resultRedirectFactory;

    protected $_customerSession;

    protected $_messageManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        CheckoutSession                                      $session,
        CustomerSession                                      $customerSession,
        Context                                              $context,
        PageFactory                                          $resultPageFactory,
        Cart                                                 $checkoutCart,
        \Magento\Framework\Message\ManagerInterface          $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Psr\Log\LoggerInterface                             $log
    )
    {
        $this->_context = $context;
        $this->_log = $log;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_checkoutCart = $checkoutCart;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
    }

    /**
     * Company List page.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $items = $this->_session->getQuote()->getAllItems();

        foreach ($items as $item) {
            $this->_checkoutCart->removeItem($item->getItemId());
        }

        $this->_checkoutCart->getQuote()->setExtShippingInfo("");
        $this->_checkoutCart->save();

        $message = __('Carrito vaciado exitosamente');
        $this->_messageManager->addSuccessMessage($message);

        $resultRedirect = $this->_resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart');

        return $resultRedirect;
    }
}
