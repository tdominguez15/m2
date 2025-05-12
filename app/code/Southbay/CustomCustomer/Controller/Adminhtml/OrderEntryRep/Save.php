<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\OrderEntryRep;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;

    private $factory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                                            $context,
        PageFactory                                                                        $resultPageFactory,
        \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig\CollectionFactory $collectionFactory,
        \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig                   $repository,
        \Southbay\CustomCustomer\Model\OrderEntryRepConfigFactory                          $factory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $params = $this->getRequest()->getParams();

        $id = $params['fields']['entity_id'] ?? null;
        $user_code = $params['fields']['magento_user_code'];
        $sold_to_ids = $params['fields']['southbay_customer_config_sold_to_ids'];
        $can_approve_at_once = $params['fields']['can_approve_at_once'];

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\CustomCustomer\Model\OrderEntryRepConfig $item
             */
            $item = $collection->getItemById($id);
        } else {
            /**
             * @var \Southbay\CustomCustomer\Model\OrderEntryRepConfig $item
             */
            $item = $this->factory->create();
            $item->setUserCode($user_code);
        }

        if (empty($sold_to_ids)) {
            $item->setSoldToIds(null);
        } else {
            $item->setSoldToIds(implode(',', $sold_to_ids));
        }

        $item->setCanApproveAtOnce($can_approve_at_once);

        $this->repository->save($item);

        $this->messageManager->addSuccessMessage(__('Datos guardados exitosamente.'));

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

