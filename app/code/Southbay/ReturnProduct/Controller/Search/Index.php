<?php

namespace Southbay\ReturnProduct\Controller\Search;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Southbay\CustomCustomer\Helper\SouthbayCustomerHelper;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository;

class Index implements HttpPostActionInterface
{
    private $context;
    private $log;
    private $factory;
    private $repository_item;
    private $customerHelper;
    private $customerSession;

    public function __construct(Context                       $context,
                                \Psr\Log\LoggerInterface      $log,
                                JsonFactory                   $factory,
                                CustomerSession               $customerSession,
                                SouthbayCustomerHelper        $customerHelper,
                                SouthbayInvoiceItemRepository $repository_item)
    {
        $this->context = $context;
        $this->customerHelper = $customerHelper;
        $this->log = $log;
        $this->factory = $factory;
        $this->repository_item = $repository_item;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $return_type = $this->context->getRequest()->getParam('return_type');
        $sold_to_id = $this->context->getRequest()->getParam('sold_to_id');
        $search = $this->context->getRequest()->getParam('search');
        $page = $this->context->getRequest()->getParam('page');

        $email = $this->customerSession->getCustomer()->getEmail();
        $sold_to = $this->customerHelper->getSoldToById($email, $sold_to_id);

        $result = $this->factory->create();

        if (is_null($sold_to)) {
            $result->setData(['list' => [], 'total_pages' => 0]);
            return $result;
        }

        /*
        $data = $this->repository_item->searchBySku(
            trim($search),
            $return_type,
            $sold_to->getCustomerCode(),
            $sold_to->getOldCustomerCode(),
            $sold_to->getCountryCode(),
            $page,
            3000);
        */

        $data = $this->repository_item->searchBySku(
            trim($search),
            $return_type,
            $sold_to->getCustomerCode(),
            $sold_to->getCountryCode(),
            $page,
            3000);

        $result->setData($data);

        return $result;
    }
}
