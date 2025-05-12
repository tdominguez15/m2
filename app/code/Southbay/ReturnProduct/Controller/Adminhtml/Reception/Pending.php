<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Reception;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

/**
 * Edit form controller
 */
class Pending extends \Magento\Backend\App\Action
{
    private $_resultFactory;
    private $repository;
    private $log;
    private $_context;

    public function __construct(Context                         $context,
                                JsonFactory                     $resultFactory,
                                SouthbayReturnProductRepository $repository,
                                \Psr\Log\LoggerInterface        $log
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_resultFactory = $resultFactory;
        $this->repository = $repository;
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
            $collection = $this->repository->allPendingReception($page);
        } else {
            $collection = $this->repository->searchPendingReception($search, $page);
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
            $items[] = [
                'id' => $item->getId(),
                'type' => $this->repository->getTypeName($item->getType()),
                'total_packages' => $item->getLabelTotalPackages(),
                'customer' => $item->getCustomerName()
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
