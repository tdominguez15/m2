<?php

namespace Southbay\ReturnProduct\Block\Frontend;

use Magento\Framework\View\Element\Template;

class MyReturn extends \Magento\Framework\View\Element\Template
{
    protected $repository;
    protected $items_repository;
    private $context;
    private $invoice_repository;
    private $invoice_item_repository;

    private $southbay_helper;

    private $control_qa_item_collection;

    public function __construct(Template\Context                                                                             $context,
                                array                                                                                        $data,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceRepository                        $invoice_repository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository                    $invoice_item_repository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository                  $repository,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaItemCollection $control_qa_item_collection,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository              $items_repository,
                                \Southbay\ReturnProduct\Helper\Data                                                          $southbay_helper
    )
    {
        if (empty($data)) {
            $data = [];
        }

        parent::__construct($context, $data);
        $this->repository = $repository;
        $this->items_repository = $items_repository;
        $this->context = $context;
        $this->invoice_repository = $invoice_repository;
        $this->invoice_item_repository = $invoice_item_repository;
        $this->southbay_helper = $southbay_helper;
        $this->control_qa_item_collection = $control_qa_item_collection;
    }

    public function getItemId($item)
    {
        if ($item->getType() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD) {
            if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT) {
                return '--';
            }
        }

        return $item->getId();
    }

    public function getReturnProduct()
    {
        $id = $this->context->getRequest()->getParam('id');
        if (empty($id)) {
            throw new \Exception('Id param not found');
        }

        $return_product = $this->repository->findById($id);

        if (empty($return_product)) {
            throw new \Exception('Field not found');
        }

        $items = $this->items_repository->findByReturnId($id);

        if (empty($items)) {
            throw new \Exception('Field without items');
        }

        $_items = [];
        $map_reason = $this->southbay_helper->getReasonReturn(true);
        $map_reason_reject = $this->southbay_helper->getReasonReject(true);

        $control_qa_items = $this->control_qa_item_collection->addFieldToFilter(
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem::ENTITY_RETURN_ID, ['eq' => $id]
        );

        $control_qa_items->load();
        $control_qa_map = [];
        $control_qa = [
            'total_real' => 0,
            'total_rejected' => 0,
            'total_accepted' => 0,
            'total_extra' => 0,
            'total_missing' => 0
        ];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem $item_control_qa
         */
        foreach ($control_qa_items->getItems() as $item_control_qa) {
            $key = $item_control_qa->getSku() . '.' . $item_control_qa->getSize();
            $control_qa_map[$key] = $item_control_qa;
            $control_qa['total_real'] += $item_control_qa->getQtyReal();
            $control_qa['total_rejected'] += $item_control_qa->getQtyReject();
            $control_qa['total_accepted'] += $item_control_qa->getQtyAccepted();
            $control_qa['total_extra'] += $item_control_qa->getQtyExtra();
            $control_qa['total_missing'] += $item_control_qa->getQtyMissing();
        }

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem $item
         */
        foreach ($items as $item) {
            $invoice = $this->invoice_repository->findById($item->getInvoiceid());

            if (is_null($invoice)) {
                throw new \Exception('Item without invoice');
            }

            $invoice_item = $this->invoice_item_repository->findById($item->getInvoiceItemId());

            if (is_null($invoice_item)) {
                throw new \Exception('Item without invoice');
            }

            $reasons = [];

            if ($return_product->getType() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL) {
                if (!empty($item->getReasonsCode())) {
                    $codes = explode(',', $item->getReasonsCode());
                    foreach ($codes as $code) {
                        $reasons[] = $map_reason[$code];
                    }
                }
            }

            $reasons_rejected = [];
            $reasons_text = '';
            $key = $item->getSku() . '.' . $item->getSize();

            if (isset($control_qa_map[$key])) {
                $control_qa_item = $control_qa_map[$key];
                $reasons_text = $control_qa_item->getReasonText();
                if (!empty($control_qa_item->getReasonCodes())) {
                    $codes = explode(',', $control_qa_item->getReasonCodes());
                    foreach ($codes as $code) {
                        if (isset($map_reason_reject[$code])) {
                            $reasons_rejected[] = $code . '-' . $map_reason_reject[$code];
                        }
                    }
                }
            }

            $_items[] = [
                'southbay_int_invoice_num' => $invoice->getIntInvoiceNum(),
                'southbay_invoice_ref' => $invoice->getInvoiceRef(),
                'southbay_invoice_item_sku' => $item->getSku(),
                'southbay_invoice_item_name' => $item->getName(),
                'southbay_invoice_item_size_code' => $invoice_item->getSize(),
                'southbay_return_item_unit_price' => $item->getNetUnitPrice(),
                'southbay_return_item_price' => $item->getNetAmount(),
                'southbay_return_item_qty' => $item->getQty(),
                'southbay_return_item_reasons_text' => $item->getReasonsText(),
                'southbay_return_item_reasons' => $reasons,
                'southbay_return_item_qty_real' => $item->getQtyReal(),
                'southbay_return_item_qty_extra' => $item->getQtyExtra(),
                'southbay_return_item_qty_missing' => $item->getQtyMissing(),
                'southbay_return_item_qty_accepted' => $item->getQtyAccepted(),
                'southbay_return_item_qty_rejected' => $item->getQtyRejected(),
                'southbay_return_item_rejected_reasons' => $reasons_rejected,
                'southbay_return_item_rejected_text' => $reasons_text
            ];
        }

        $has_docs = $this->repository->hasSapDoc($return_product->getId());

        $this->_logger->debug('test', ['ff' => $return_product->getData()]);

        return [
            'field' => $return_product,
            'control_qa' => $control_qa,
            'has_docs' => $has_docs,
            'docs' => ($has_docs ? $this->repository->getSapDocs($return_product->getId()) : []),
            'items' => $_items
        ];
    }

    public function getTypeName($type)
    {
        return $this->repository->getTypeName($type);
    }

}
