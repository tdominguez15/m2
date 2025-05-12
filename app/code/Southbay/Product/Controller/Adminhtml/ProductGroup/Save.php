<?php

namespace Southbay\Product\Controller\Adminhtml\ProductGroup;

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
        Context                                                                      $context,
        PageFactory                                                                  $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductGroup\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductGroup                   $repository,
        \Southbay\Product\Model\SouthbayProductGroupFactory                          $factory
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
        $code = $params['fields']['code'];
        $type = $params['fields']['type'];
        $name = $params['fields']['name'];

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\Product\Model\SouthbayProductGroup $item
             */
            $item = $collection->getItemById($id);
        } else {
            /**
             * @var \Southbay\Product\Model\SouthbayProductGroup $item
             */
            $item = $this->factory->create();
            $item->setType($type);
            $item->setCode($code);
        }

        $item->setName($name);
        $this->repository->save($item);

        $this->messageManager->addSuccessMessage(__('Datos guardados exitosamente.'));

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

