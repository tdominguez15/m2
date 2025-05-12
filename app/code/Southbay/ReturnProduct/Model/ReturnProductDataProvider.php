<?php

namespace Southbay\ReturnProduct\Model;

use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class ReturnProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    protected $log;
    private $context;
    protected $returnProductRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Backend\App\Action\Context $context,
        SouthbayReturnProductRepository $returnProductRepository,
        \Psr\Log\LoggerInterface $log,
        array $meta = [],
        array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->log = $log;
        $this->context = $context;
        $this->returnProductRepository = $returnProductRepository;
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->createCollection();

        }
        return $this->collection;
    }

    protected function createCollection()
    {
        return $this->returnProductRepository->getDataproviderCollection();
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

        $this->loadedData = [
            'items' => [],
            'totalRecords' => 0
        ];

        $items = $this->getCollection()->getItems();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $item
         */
        foreach ($items as $item) {
            $data = $item->toArray();
            $data['id_field_name'] = 'southbay_return_id';
            $data['link_label'] = 'Ver detalle';
            $data['link'] = $this->context->getBackendUrl()->getUrl(
                'southbay_return_product/confirmation/view',
                ['id' => $data['southbay_return_id']]
            );

            $data['has_docs'] = false;
            $total_docs = $this->returnProductRepository->getTotalDocuments($data['southbay_return_id']);

            if ($this->returnProductRepository->hasSapDoc($data['southbay_return_id'])) {
                $data['has_docs'] = true;
            }

            $data['has_docs'] = ($data['has_docs'] ? __('Si') : __('No'));
            $data['total_docs'] = $total_docs['total_success'] . '/' . $total_docs['total'];

            if ($total_docs['total'] > 0) {
                $data['total_docs_link'] = $this->context->getBackendUrl()->getUrl(
                    'southbay_return_product/confirmation/sapview',
                    ['id' => $data['southbay_return_id']]
                );
            } else {
                $data['total_docs'] = __('Reintentar');
                $data['total_docs_link'] = $this->context->getBackendUrl()->getUrl(
                    'southbay_return_product/confirmation/confirm',
                    ['id' => $data['southbay_return_id']]
                );
            }

            $this->loadedData['items'][] = $data;
        }

        $this->loadedData['totalRecords'] = $this->getCollection()->getSize();

        return $this->loadedData;
    }
}
