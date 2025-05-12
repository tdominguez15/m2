<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Config;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context                                                          $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnConfig                             $repository,
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnConfigCollectionFactory $collectionFactory,
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
        $params = $this->getRequest()->getParams();
        $this->log->debug('Saving config:', ['params' => $params]);

        $type = $params['southbay_return_config']['southbay_return_type'];
        $country_code = $params['southbay_return_config']['southbay_return_country_code'];
        $max_year_history = intval($params['southbay_return_config']['southbay_return_max_year_history']);
        $order = $params['southbay_return_config']['southbay_return_order'];
        $label_text = $params['southbay_return_config']['southbay_return_label_text'];
        $available_automatic_approval = $params['southbay_return_config']['southbay_return_available_automatic_approval'];
        $max_automatic_amount = $params['southbay_return_config']['southbay_return_max_automatic_amount'];

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_TYPE, ['eq' => $type]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_COUNTRY_CODE, ['eq' => $country_code]);
        $collection->load();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig $item
         */
        $item = $collection->getFirstItem();
        $item->setType($type);
        $item->setCountryCode($country_code);
        $item->setMaxYearHistory($max_year_history);
        $item->setOrder($order);
        $item->setLabelText($label_text);
        $item->setAvailableAutomaticApproval(($available_automatic_approval == 'true' ? 1 : 0));
        $item->setMaxAutomaticAmount($max_automatic_amount);
        $this->repository->save($item);

        $resultRedirect = $this->resultRedirectFactory->create();

        $this->messageManager->addSuccessMessage(__('Configuración guardada'));
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
