<?php

namespace Southbay\CustomCustomer\Controller\Adminhtml\OrderEntryNotification;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;

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
        Context                                                                                     $context,
        JsonFactory                                                                                 $resultFactory,
        \Southbay\CustomCustomer\Model\ResourceModel\OrderEntryNotificationConfig\CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface                                                                    $log
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
        $country_code = $params['fields']['southbay_country_code'];
        $function_code = $params['fields']['southbay_function_code'];

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\CustomCustomer\Model\OrderEntryNotificationConfig $item
             */
            $item = $collection->getItemById($id);

            if (is_null($item)) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('No existe la configuración que intenta modificar');
            }
        } else {
            $collection->addFieldToFilter('southbay_function_code', $function_code);
            $collection->addFieldToFilter('southbay_country_code', $country_code);

            if ($collection->getSize() > 0) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('Ya existe una configuración para la funcionalidad y el país que selecciono');
            }

            if ($function_code != ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('Las notificaciones están disponibles solo para at once');
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
