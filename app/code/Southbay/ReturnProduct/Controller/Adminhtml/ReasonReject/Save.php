<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ReasonReject;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context                                                          $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReasonReject                             $repository,
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReasonRejectCollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface                                                                     $log
    )
    {
        $this->log = $log;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $this->log->debug('Saving reason reject:', ['params' => $params]);

        if (isset($params['fields']['southbay_reason_reject_id'])) {
            $id = $params['fields']['southbay_reason_reject_id'];
        } else {
            $id = null;
        }

        $country_code = $params['fields']['southbay_reason_reject_country_code'];
        $code = $params['fields']['southbay_reason_reject_code'];
        $name = $params['fields']['southbay_reason_reject_name'];

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();

        if (empty($id)) {
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReject::ENTITY_CODE, ['eq' => $code]);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReject::ENTITY_COUNTRY_CODE, ['eq' => $country_code]);
            $collection->load();
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReasonReject $item
             */
            $item = $collection->getFirstItem();
        } else {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReasonReject $item
             */
            $item = $collection->getItemById($id);


            if (is_null($item)) {
                $this->messageManager->addError(__('No existe el registro'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        $item->setCountryCode($country_code);
        $item->setCode($code);
        $item->setName($name);
        $this->repository->save($item);

        $this->messageManager->addSuccessMessage(__('Datos guardados'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
