<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class ApprovalDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    private $collection_factory;
    protected $loadedData;
    private $log;
    private $context;
    private $returnProductRepository;

    public function __construct($name,
        $primaryFieldName,
        $requestFieldName,
                                \Magento\Backend\App\Action\Context $context,
                                SouthbayReturnProductRepository $returnProductRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalCollectionFactory $collection_factory,
                                \Psr\Log\LoggerInterface $log,
                                array $meta = [],
                                array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection_factory = $collection_factory;
        $this->log = $log;
        $this->context = $context;
        $this->returnProductRepository = $returnProductRepository;
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->collection_factory->create();
        }
        return $this->collection;
    }


    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];

        $id = $this->context->getRequest()->getParam('id');

        if ($id) {
            $item = $this->collection->getItemById($id);
            if (!is_null($item)) {
                $return_product = $this->returnProductRepository->findById($item->getReturnId());
                $data = $item->getData();
                $data['southbay_return_product_type'] = $this->returnProductRepository->getTypeName($return_product->getType());
                $data['southbay_return_product_customer'] = $return_product->getCustomerName();
                $data['southbay_return_financial_approval_approved'] = ($data['southbay_return_financial_approval_approved'] ? 'approval' : 'reject');
                $this->loadedData[$id] = [
                    'link' => $this->context->getBackendUrl()->getUrl('southbay_return_product/confirmation/view', ['id' => $item->getReturnId()]),
                    'fields' => ['edit_form_fields' => $data]
                ];

                $this->log->debug('loadedData', ['l' => $this->loadedData]);
            }
        }

        return $this->loadedData;
    }
}
