<?php

namespace Southbay\ReturnProduct\Model;

class ControlQaDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    private $collection_factory;
    private $items_collection_factory;
    private $returnProductRepository;
    private $itemsReturnProductRepository;
    protected $loadedData;
    private $log;
    private $context;

    public function __construct($name,
        $primaryFieldName,
        $requestFieldName,
                                \Magento\Backend\App\Action\Context $context,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $returnProductRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository $itemsReturnProductRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaCollectionFactory $collection_factory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaItemCollectionFactory $items_collection_factory,
                                \Psr\Log\LoggerInterface $log,
                                array $meta = [],
                                array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection_factory = $collection_factory;
        $this->items_collection_factory = $items_collection_factory;
        $this->returnProductRepository = $returnProductRepository;
        $this->itemsReturnProductRepository = $itemsReturnProductRepository;
        $this->log = $log;
        $this->context = $context;
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
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa $item
             */
            $item = $this->collection->getItemById($id);

            if ($item) {
                $return_product = $this->returnProductRepository->findById($item->getReturnId());
                $data = $item->getData();
                $data['southbay_return_product_type'] = $this->returnProductRepository->getTypeName($return_product->getType());
                $data['southbay_return_product_customer'] = $return_product->getCustomerName();
                $southbay_return_control_qa_items = [];

                $items_collection = $this->items_collection_factory->create();
                $items_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem::ENTITY_CONTROL_QA_ID, $item->getId());
                $items_collection->load();
                $items = $items_collection->getItems();
                $items_return_product = $this->itemsReturnProductRepository->findByReturnIdAndGroupBySkuAndSize($return_product->getId());
                $map = [];

                foreach ($items_return_product as $_item) {
                    $map[$_item['key']] = $_item;
                }

                /**
                 * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem $_item
                 */
                foreach ($items as $_item) {
                    $key = $_item->getSku() . '.' . $_item->getSize();

                    if (isset($map[$key])) {
                        $__item = $map[$key];
                        $__item['southbay_return_qty_accepted'] = $_item->getQtyAccepted();
                        $__item['southbay_return_qty_real'] = $_item->getQtyReal();
                        $__item['southbay_return_qty_extra'] = $_item->getQtyExtra();
                        $__item['southbay_return_qty_missing'] = $_item->getQtyMissing();
                        $__item['southbay_return_qty_rejected'] = $_item->getQtyExtra();
                        $reason_codes = [];

                        if (!empty($_item->getReasonCodes())) {
                            $reason_codes = explode(',', $_item->getReasonCodes());
                        }

                        $__item['southbay_return_item_reject_reason_codes'] = $reason_codes;

                        $__item['southbay_return_item_reject_reason_text'] = $_item->getReasonText();
                        unset($map[$key]);
                        $southbay_return_control_qa_items[] = $__item;
                    }
                }

                foreach ($map as $__item) {
                    $southbay_return_control_qa_items[] = $__item;
                }

                $data['items'] = $southbay_return_control_qa_items;
                $this->loadedData[$item->getId()]['main_fieldset'] = ['fields' => $data, 'items_fieldset' => ['southbay_return_control_qa_items' => json_encode($southbay_return_control_qa_items)]];
            }
        }

        return $this->loadedData;
    }
}
