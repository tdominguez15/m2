<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ControlQa;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository;

/**
 * Edit form controller
 */
class Pending extends \Magento\Backend\App\Action
{
    private $_resultFactory;
    private $repository;
    private $log;
    private $_context;
    private $items_repository;

    public function __construct(Context                             $context,
                                JsonFactory                         $resultFactory,
                                SouthbayReturnProductRepository     $repository,
                                SouthbayReturnProductItemRepository $items_repository,
                                \Psr\Log\LoggerInterface            $log
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_resultFactory = $resultFactory;
        $this->repository = $repository;
        $this->items_repository = $items_repository;
        $this->log = $log;
    }

    public function execute()
    {
        $params = $this->_context->getRequest()->getParams();

        $this->log->debug('Search pending return product', ['params' => $params]);

        $search = '';
        $page = null;

        $items = [];
        $has_more = false;

        if (isset($params['term'])) {
            $search = $params['term'];
        }

        if (isset($params['page'])) {
            $page = intval($params['page']);
        }

        if (is_null($page)) {
            $page = 1;
        } else if ($page < 1) {
            $page = 1;
        }

        if (empty($search)) {
            $collection = $this->repository->allPendingControlQa($page);
        } else {
            $collection = $this->repository->searchPendingControlQa($search, $page);
        }

        $last_page = $collection->getLastPageNumber();

        if ($last_page > 1 && $page < $last_page) {
            $has_more = true;
        }

        $_items = $collection->getItems();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $item
         */
        foreach ($_items as $item) {
            $return_product_items = $this->items_repository->findByReturnIdAndGroupBySkuAndSize($item->getId());

            $items[] = [
                'id' => $item->getId(),
                'type' => $this->repository->getTypeName($item->getType()),
                'customer' => $item->getCustomerName(),
                'items' => $return_product_items
            ];
        }

        $result = $this->_resultFactory->create();
        $result->setData([
            'items' => $items,
            'more' => $has_more
        ]);

        return $result;
    }
}
