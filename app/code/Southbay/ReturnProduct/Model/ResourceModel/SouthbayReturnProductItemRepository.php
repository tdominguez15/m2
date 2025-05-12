<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnBalanceItem as SouthbayReturnBalanceItemRepository;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnBalanceItemCollectionFactory;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnProductItemCollectionFactory;
use Southbay\ReturnProduct\Model\SouthbayReturnBalanceItemFactory;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem;

class SouthbayReturnProductItemRepository
{
    private $balanceItemCollectionFactory;
    private $repository_item;
    private $returnItemCollectionFactory;
    private $return_balance_factory;
    private $return_balance_repository;
    private $log;
    private $cache;
    private $helper;

    public function __construct(SouthbayReturnBalanceItemFactory                                      $return_balance_factory,
                                SouthbayReturnBalanceItemCollectionFactory                            $balanceItemCollectionFactory,
                                SouthbayReturnBalanceItemRepository                                   $return_balance_repository,
                                SouthbayReturnProductItemCollectionFactory                            $returnItemCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItem $repository_item,
                                \Magento\Framework\Model\Context                                      $context,
                                \Southbay\ReturnProduct\Helper\Data                                   $helper,
                                \Psr\Log\LoggerInterface                                              $log)
    {
        $this->repository_item = $repository_item;
        $this->return_balance_factory = $return_balance_factory;
        $this->return_balance_repository = $return_balance_repository;
        $this->balanceItemCollectionFactory = $balanceItemCollectionFactory;
        $this->helper = $helper;
        $this->log = $log;
        $this->cache = $context->getCacheManager();
        $this->returnItemCollectionFactory = $returnItemCollectionFactory;
    }

    public function findByReturnId($id)
    {
        $collection = $this->returnItemCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem::ENTITY_RETURN_ID, ['eq' => $id]);
        $collection->load();

        return $collection->getItems();
    }

    public function findByReturnIdAndGroupBySkuAndSize($id)
    {
        $map_items = [];
        $return_product_items = $this->findByReturnId($id);

        if (empty($return_product_items)) {
            return [];
        }

        $_return_product_items = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem $return_product_item
         */
        foreach ($return_product_items as $return_product_item) {
            $key = $return_product_item->getSku() . '.' . $return_product_item->getSize();
            if (!isset($map_items[$key])) {
                $map_items[$key] = [
                    'key' => $key,
                    'southbay_return_item_sku' => $return_product_item->getSku(),
                    'southbay_return_item_size' => $return_product_item->getSize(),
                    'southbay_return_item_name' => $return_product_item->getName(),
                    'southbay_return_item_reasons_text' => $return_product_item->getReasonsText(),
                    'southbay_return_item_qty' => $return_product_item->getQty(),
                    'southbay_return_qty_accepted' => $return_product_item->getQty(),
                    'southbay_return_qty_real' => $return_product_item->getQty(),
                    'southbay_return_qty_extra' => 0,
                    'southbay_return_qty_missing' => 0,
                    'southbay_return_qty_rejected' => 0,
                    'southbay_return_item_reject_reason_codes' => '',
                    'southbay_return_item_reject_reason_text' => ''
                ];
            } else {
                $map_items[$key]['southbay_return_item_qty'] += $return_product_item->getQty();
                $map_items[$key]['southbay_return_qty_real'] += $return_product_item->getQty();
                $map_items[$key]['southbay_return_qty_accepted'] += $return_product_item->getQty();
            }
        }

        foreach ($map_items as $group_item) {
            $_return_product_items[] = $group_item;
        }

        return $_return_product_items;
    }

    public function findByReturnIdAndGetIds($id, $sku, $size)
    {
        /**
         * @var AbstractCollection $collection
         */
        $collection = $this->returnItemCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem::ENTITY_SKU, ['eq' => $sku]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem::ENTITY_SIZE, ['eq' => $size]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem::ENTITY_RETURN_ID, ['eq' => $id]);
        $collection->join(['invoice' => \Southbay\ReturnProduct\Api\Data\SouthbayInvoice::TABLE],
            "invoice.southbay_invoice_id = main_table.southbay_invoice_id"
        );
        $collection
            ->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns("main_table.southbay_invoice_item_id");


        $collection->setOrder("invoice.southbay_invoice_date", 'ASC');

        return $collection->getAllIds();
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem|null
     */
    public function findById($id)
    {
        $collection = $this->returnItemCollectionFactory->create();
        return $collection->getItemById($id);
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem|null
     */
    public function findByInvoiceItemId($id)
    {
        $collection = $this->returnItemCollectionFactory->create();
        return $collection->getItemByColumnValue(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem::ENTITY_INVOICE_ITEM_ID, $id);
    }

    public function cancelReturnProduct($id)
    {
        $items = $this->findByReturnId($id);

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem $item
         */
        foreach ($items as $item) {
            $item_balance = $this->findBalanceByInvoiceItemId($item->getInvoiceItemId());

            if (is_null($item_balance)) {
                continue;
            }

            if($item->getQtyAccepted() > 0) {
                $qty = $item->getQtyAccepted();
            } else {
                $qty = $item->getQty();
            }

            $item_balance->setTotalReturn($item_balance->getTotalReturn() - $qty);
            $item_balance->setTotalAvailable($item_balance->getTotalAvailable() + $qty);

            $this->return_balance_repository->save($item_balance);

            $identifier = \Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG . '_' . $item->getInvoiceItemId();
            $this->cache->save(serialize($item_balance), $identifier, [\Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG]);
        }
    }

    /**
     * @param $id
     * @param $map_item
     * @param $last
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function updateByControlQa($id, $map_item, $last)
    {
        $item = $this->findById($id);

        $item_balance = $this->findBalanceByInvoiceItemId($item->getInvoiceItemId());

        if (is_null($item_balance)) {
            $this->log->error('Invoice item without balance', ['invoice_item_id' => $id]);
            return null;
        }

        $total_returned = $item_balance->getTotalReturn();
        $total_available = $item_balance->getTotalAvailable();

        if ($item->getQtyRejected() > 0) {
            $total_available -= $item->getQtyRejected();
            $total_returned += $item->getQtyRejected();
        }

        $item->setQtyReal(0);
        $item->setQtyExtra(0);
        $item->setQtyMissing(0);
        $item->setQtyAccepted(0);
        $item->setQtyRejected(0);

        if ($last) {
            if ($map_item['qty_real'] > 0) {
                $item->setQtyReal($map_item['qty_real']);

                if ($map_item['qty_extra'] > 0) {
                    $item->setQtyExtra($map_item['qty_extra']);
                } else if ($map_item['qty_missing'] > 0) {
                    $item->setQtyMissing($map_item['qty_missing']);
                }
            } else {
                $item->setQtyMissing($item->getQty());
            }
        } else {
            if ($map_item['qty_real'] > 0) {
                if ($item->getQty() > $map_item['qty_real']) {
                    $item->setQtyReal($map_item['qty_real']);
                    $map_item['qty_real'] = 0;
                } else {
                    $map_item['qty_real'] = $map_item['qty_real'] - $item->getQty();
                    $item->setQtyReal($item->getQty());
                }
            } else {
                $item->setQtyMissing($item->getQty());
            }
        }

        if ($map_item['qty_real'] > 0) {
            $accepted = $item->getQty() - $item->getQtyMissing();
            $rejected = 0;

            if ($map_item['qty_reject'] > 0) {
                if ($accepted > $map_item['qty_reject']) {
                    $item->setQtyRejected($map_item['qty_reject']);
                    $accepted -= $item->getQtyRejected();
                    $rejected = $item->getQtyRejected();

                    $map_item['qty_reject'] = 0;
                } else {
                    $rejected = $accepted;
                    $accepted = 0;
                    $map_item['qty_reject'] = $map_item['qty_reject'] - $accepted;
                }
            }

            $item->setQtyAccepted($accepted);
            $item->setQtyRejected($rejected);
            $item->setAmountAccepted($item->getNetUnitPrice() * $item->getQtyAccepted());
        }

        $this->repository_item->save($item);

        $total_available += $item->getQtyRejected() + $item->getQtyMissing();
        $total_returned -= $item->getQtyRejected() + $item->getQtyMissing();

        $identifier = \Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG . '_' . $item->getId();

        $item_balance->setTotalAvailable($total_available);
        $item_balance->setTotalReturn($total_returned);

        $this->return_balance_repository->save($item_balance);

        $this->cache->save(serialize($item_balance), $identifier, [\Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG]);

        return [
            'item' => $item,
            'map_item' => $map_item
        ];
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem $model
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem $invoice_item
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem
     */
    public function save($model, $invoice_item)
    {
        $balance = $this->findBalanceByInvoiceItemId($model->getInvoiceItemId());
        if (is_null($balance)) {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem $balance
             */
            $balance = $this->return_balance_factory->create();
            $balance->setInvoiceId($model->getInvoiceItemId());
            $balance->setInvoiceItemId($model->getInvoiceItemId());
            $balance->setTotalInvoiced($invoice_item->getQty());
            $balance->setTotalReturn($model->getQty());
        } else {
            $balance->setTotalReturn($balance->getTotalReturn() + $model->getQty());
        }

        $this->repository_item->save($model);

        $diff = $balance->getTotalInvoiced() - $balance->getTotalReturn();

        if ($diff > 0) {
            $balance->setTotalAvailable($diff);
        } else {
            $balance->setTotalAvailable(0);
        }

        $this->return_balance_repository->save($balance);

        $identifier = \Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG . '_' . $invoice_item->getId();
        $this->cache->save(serialize($balance), $identifier, [\Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG]);

        return $model;
    }

    /**
     * @param $invoice_item_id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem|null
     */
    public function findBalanceByInvoiceItemId($invoice_item_id)
    {
        $identifier = \Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG . '_' . $invoice_item_id;
        $item = $this->cache->load($identifier);

        if (!$item) {
            $collection = $this->findByAttributeNameBalance(SouthbayReturnBalanceItem::ENTITY_INVOICE_ITEM_ID, $invoice_item_id);
            $collection->setPageSize(1);
            $collection->setCurPage(1);

            if ($collection->getSize() > 0) {
                $item = $collection->getFirstItem();
                $this->cache->save(serialize($item), $identifier, [\Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem::CACHE_TAG]);
            } else {
                $item = null;
            }
        } else {
            $item = unserialize($item);
        }

        return $item;
    }

    /**
     * @param $id
     * @param $value
     * @return AbstractCollection
     */
    private function findByAttributeNameBalance($name, $value)
    {
        $collection = $this->balanceItemCollectionFactory->create();
        return $collection->addFieldToFilter($name, ['eq' => $value]);
    }
}
