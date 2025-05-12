<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\OrderEntryNotification;

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
        Context                                                                                     $context,
        PageFactory                                                                                 $resultPageFactory,
        \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotificationConfig\CollectionFactory $collectionFactory,
        \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotificationConfig                   $repository,
        \Southbay\CustomCustomer\Model\OrderEntryNotificationConfigFactory                          $factory
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
        $country_code = $params['fields']['southbay_country_code'];
        $function_code = $params['fields']['southbay_function_code'];
        $template_id = $params['fields']['magento_template_id'];
        $retry_after = $params['fields']['retry_after'];

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\CustomCustomer\Model\OrderEntryNotificationConfig $item
             */
            $item = $collection->getItemById($id);
        } else {
            /**
             * @var \Southbay\CustomCustomer\Model\OrderEntryNotificationConfig $item
             */
            $item = $this->factory->create();
            $item->setCountryCode($country_code);
            $item->setFunctionCode($function_code);
        }

        $item->setTemplateId($template_id);
        $item->setRetryAfter($retry_after);

        $this->repository->save($item);

        $this->messageManager->addSuccessMessage(__('Datos guardados exitosamente.'));

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

