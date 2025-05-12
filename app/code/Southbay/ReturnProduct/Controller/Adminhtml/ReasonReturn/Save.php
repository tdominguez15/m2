<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ReasonReturn;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context                                                          $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReasonReturn                             $repository,
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReasonReturnCollectionFactory $collectionFactory,
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
        $this->log->debug('Saving reason return:', ['params' => $params]);

        if (isset($params['southbay_reason_return']['southbay_reason_return_id'])) {
            $id = $params['southbay_reason_return']['southbay_reason_return_id'];
        } else {
            $id = null;
        }

        $country_code = $params['southbay_reason_return']['southbay_reason_return_country_code'];
        $code = $params['southbay_reason_return']['southbay_reason_return_code'];
        $name = $params['southbay_reason_return']['southbay_reason_return_name'];

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();

        if (empty($id)) {
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn::ENTITY_CODE, ['eq' => $code]);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn::ENTITY_COUNTRY_CODE, ['eq' => $country_code]);
            $collection->load();
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn $item
             */
            $item = $collection->getFirstItem();
        } else {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn $item
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
