<?php

namespace Southbay\Product\Controller\Adminhtml\ProductGroup;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Validate extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultFactory;

    private $collectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                                      $context,
        JsonFactory                                                                  $resultFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductGroup\CollectionFactory $collectionFactory
    )
    {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $result = $this->resultFactory->create();
        $result_data = [
            'error' => false,
            'messages' => []
        ];

        $params = $this->getRequest()->getParams();

        $id = $params['fields']['entity_id'] ?? null;
        $code = $params['fields']['code'];
        $type = $params['fields']['type'];

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\Product\Model\Season $item
             */
            $item = $collection->getItemById($id);

            if (is_null($item)) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('No existe el grupo que intenta actualizar');
            }
        } else {
            $collection->addFieldToFilter('code', $code);
            $collection->addFieldToFilter('type', $type);

            if ($collection->getSize() > 0) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('Ya existe otra grupo con el mismo tipo y codigo');
            }
        }

        $result->setData($result_data);
        return $result;
    }

    public function _isAllowed()
    {
        return true;
    }
}
