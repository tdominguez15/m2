<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\ControlQa;

use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaCollectionFactory as CollectionFactory;
use Southbay\ReturnProduct\Model\ResourceModel\DataProvider\DataProviderBase;

class ControlQaDataProvider extends DataProviderBase
{

    private $items_collection_factory;
    private $itemsReturnProductRepository;
    private $helper;

    public function __construct(\Magento\Backend\App\Action\Context                                                                 $context,
                                \Southbay\ReturnProduct\Helper\Data                                                                 $helper,
                                \Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnTypeOptionsProvider                         $returnTypeOptionsProvider,
                                \Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnProductClientOptionsProvider                $clientOptionsProvider,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository                         $returnProductRepository,
                                CollectionFactory                                                                                   $collection_factory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository                     $itemsReturnProductRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaItemCollectionFactory $items_collection_factory,
                                                                                                                                    $name,
                                                                                                                                    $primaryFieldName,
                                                                                                                                    $requestFieldName,
                                \Psr\Log\LoggerInterface                                                                            $log,
                                array                                                                                               $meta = [],
                                array                                                                                               $data = [])
    {
        $this->collection_factory = $collection_factory;
        $this->items_collection_factory = $items_collection_factory;
        $this->itemsReturnProductRepository = $itemsReturnProductRepository;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $returnTypeOptionsProvider,
            $clientOptionsProvider,
            $returnProductRepository,
            $name,
            $primaryFieldName,
            $requestFieldName,
            $log,
            $meta,
            $data);
    }

    protected function initCollection()
    {
        $countries = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CONTROL_QA);

        if (empty($countries)) {
            $countries = ['-'];
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = parent::initCollection();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_COUNTRY_CODE, ['in' => $countries]);

        return $collection;
    }

    protected function getItem($item)
    {
        $items_collection = $this->items_collection_factory->create();
        $items_collection->addFieldToFilter(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem::ENTITY_CONTROL_QA_ID,
            $item['fields']['detail']['southbay_return_control_qa_id']);
        $items_collection->load();
        $items = $items_collection->getItems();
        $items_return_product = $this->itemsReturnProductRepository->findByReturnIdAndGroupBySkuAndSize($item['fields']['detail']['southbay_return_id']);
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
                $__item['southbay_return_qty_rejected'] = $_item->getQtyReject();
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

        $item['fields']['items'] = $southbay_return_control_qa_items;
        $item['fields']['items_fieldset'] = ['southbay_return_control_qa_items' => json_encode($southbay_return_control_qa_items)];

        $field = $this->returnProductRepository->findById($item['fields']['detail']['southbay_return_id']);
        $item['fields']['edit_mode'] = ($this->returnProductRepository->availableForEditControlQa($field) ? 'edit' : 'view');

        return $item;
    }
}
