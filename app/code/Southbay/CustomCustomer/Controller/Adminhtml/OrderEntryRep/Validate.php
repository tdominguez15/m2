<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\OrderEntryRep;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
class Validate extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultFactory;

    private $collectionFactory;

    private $log;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                                            $context,
        JsonFactory                                                                        $resultFactory,
        \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryRepConfig\CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface                                                           $log
    )
    {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->collectionFactory = $collectionFactory;
        $this->log = $log;
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
        $user_code = $params['fields']['magento_user_code'];

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\CustomCustomer\Model\OrderEntryRepConfig $item
             */
            $item = $collection->getItemById($id);

            if (is_null($item)) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('No existe el representante que intenta modificar');
            }
        } else {
            $collection->addFieldToFilter('magento_user_code', $user_code);

            if ($collection->getSize() > 0) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('Ya un representante registrado con el usuario seleccionado');
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
