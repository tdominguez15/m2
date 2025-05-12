<?php

namespace Southbay\Product\Controller\Adminhtml\Segmentation;

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
        \Southbay\Product\Model\ResourceModel\Segmentation\CollectionFactory         $collectionFactory,
        \Southbay\Product\Model\SegmentationRepository                               $repository,
        \Southbay\Product\Model\SegmentationFactory                                  $factory
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
        $label = $params['fields']['label'];

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\Product\Model\Segmentation $item
             */
            $item = $collection->getItemById($id);
        } else {
            /**
             * @var \Southbay\Product\Model\Segmentation $item
             */
            $item = $this->factory->create();
        }
        $item->setLabel($label);
        $item->setCode($code);



        $this->repository->save($item);

        $this->messageManager->addSuccessMessage(__('Datos guardados exitosamente.'));

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

