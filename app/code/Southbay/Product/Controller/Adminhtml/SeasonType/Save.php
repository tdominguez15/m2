<?php

namespace Southbay\Product\Controller\Adminhtml\SeasonType;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Southbay\Product\Api\Data\SeasonTypeInterface;

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
        Context                                                            $context,
        PageFactory                                                        $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SeasonType\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\SeasonType                   $repository,
        \Southbay\Product\Model\SeasonTypeFactory                          $factory
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
        $id = $params['fields']['type_id'] ?? null;
        $code = $params['fields']['type_code'];
        $name = $params['fields']['type_name'];
        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\Product\Model\SeasonType $item
             */
            $item = $collection->getItemById($id);
        } else {
            $item = $collection->getItemByColumnValue(SeasonTypeInterface::ENTITY_CODE, $code);
            if (is_null($item)) {
                /**
                 * @var \Southbay\Product\Model\SeasonType $item
                 */
                $item = $this->factory->create();
                $item->setSeasonTypeCode($code);
                $item->setSeasonTypeName($name);
            } else {
                $item = null;
                $this->messageManager->addErrorMessage(__('Ya existe otro registro con el mismo codigo de tipo de temporada'));
            }
        }

        if (!is_null($item)) {
            $item->setSeasonTypeName($name);
            $this->repository->save($item);

            $this->messageManager->addSuccessMessage(__('Datos guardados exitosamente.'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

