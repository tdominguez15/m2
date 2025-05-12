<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Approval;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Controller\Result\JsonFactory;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApprovalUsersRepository;
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
    private $southbay_helper;
    private $southbayReturnFinancialApprovalUsersRepository;

    public function __construct(Context                                        $context,
                                JsonFactory                                    $resultFactory,
                                SouthbayReturnProductRepository                $repository,
                                SouthbayReturnProductItemRepository            $items_repository,
                                \Southbay\ReturnProduct\Helper\Data            $southbay_helper,
                                SouthbayReturnFinancialApprovalUsersRepository $southbayReturnFinancialApprovalUsersRepository,
                                \Psr\Log\LoggerInterface                       $log
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_resultFactory = $resultFactory;
        $this->repository = $repository;
        $this->items_repository = $items_repository;
        $this->log = $log;
        $this->southbay_helper = $southbay_helper;
        $this->southbayReturnFinancialApprovalUsersRepository = $southbayReturnFinancialApprovalUsersRepository;
    }

    public
    function execute()
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
            $collection = $this->repository->allPendingApproval($page);
        } else {
            $collection = $this->repository->searchPendingApproval($search, $page);
        }

        $last_page = $collection->getLastPageNumber();

        if ($last_page > 1 && $page < $last_page) {
            $has_more = true;
        }

        $_items = $collection->getItems();
        $map_exchanges = [];
        $current_user_id = $this->_auth->getUser()->getId();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $item
         */
        foreach ($_items as $item) {
            if (!isset($map_exchanges[$item->getCountryCode()])) {
                $exchange = $this->southbay_helper->getLastExchange($item->getCountryCode());

                if (is_null($exchange)) {
                    throw new \Exception(__('Falta cargar tipo de cambio'));
                }

                $map_exchanges[$item->getCountryCode()] = $exchange;
            } else {
                $exchange = $map_exchanges[$item->getCountryCode()];
            }

            $_item = $this->southbay_helper->getApprovalPendingItem($item, $this->repository->getTypeName($item->getType()), $exchange->getExchange());
            $_item['link'] = $this->_context->getBackendUrl()->getUrl('southbay_return_product/confirmation/view', ['id' => $item->getId()]);
            $approval_user = $this->southbayReturnFinancialApprovalUsersRepository->findUserByReturnId($current_user_id, $_item['id']);

            if ($approval_user) {
                if (is_null($approval_user->getApproved())) {
                    $items[] = $_item;
                }
            } else {
                $items[] = $_item;
            }
        }

        $result = $this->_resultFactory->create();
        $result->setData([
            'items' => $items,
            'more' => $has_more
        ]);

        return $result;
    }
}
