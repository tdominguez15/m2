<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SouthbayReturnControlQaRepository
{
    private $log;
    private $repository;
    private $factory;
    private $collectionFactory;
    private $itemCollectionFactory;
    private $return_product_repository;
    private $item_repository;
    private $resourceConnection;
    private $item_factory;
    private $return_product_item_repository;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQa                                 $repository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQaItem                             $item_repository,
                                \Southbay\ReturnProduct\Model\SouthbayReturnControlQaFactory                                        $factory,
                                \Southbay\ReturnProduct\Model\SouthbayReturnControlQaItemFactory                                    $item_factory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaCollectionFactory     $collectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaItemCollectionFactory $itemCollectionFactory,
                                SouthbayReturnProductRepository                                                                     $return_product_repository,
                                SouthbayReturnProductItemRepository                                                                 $return_product_item_repository,
                                ResourceConnection                                                                                  $resourceConnection,
                                \Psr\Log\LoggerInterface                                                                            $log)
    {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->return_product_item_repository = $return_product_item_repository;
        $this->collectionFactory = $collectionFactory;
        $this->item_repository = $item_repository;
        $this->return_product_repository = $return_product_repository;
        $this->resourceConnection = $resourceConnection;
        $this->log = $log;
        $this->item_factory = $item_factory;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa|null
     */
    public function findByReturnProductId($id)
    {
        /**
         * @var \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_RETURN_ID, $id);

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa|null
     */
    public function findById($id)
    {
        $collection = $this->collectionFactory->create();
        return $collection->getItemById($id);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $return_product
     * @param $data
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa|false
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save($return_product, $data)
    {
        $connection = $this->resourceConnection->getConnection();
        $error = false;

        try {
            $connection->beginTransaction();

            $collection = $this->findByAttributeName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::ENTITY_RETURN_ID, $data['return_id']);
            $collection->setPageSize(1);
            $collection->setCurPage(1);
            $collection->load();

            if ($collection->count() == 0 && is_null($data['control_qa_id'])) {
                /**
                 * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa $model
                 */
                $model = $this->factory->create();
            } else if ($collection->count() > 0 && !is_null($data['control_qa_id'])) {
                /**
                 * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa $model
                 */
                $model = $collection->getFirstItem();
                if ($model->getId() != $data['control_qa_id']) {
                    $connection->rollBack();
                    return false;
                } else {
                    /**
                     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $item_collection
                     */
                    $item_collection = $this->itemCollectionFactory->create();
                    $item_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem::ENTITY_CONTROL_QA_ID, $model->getId());
                    $item_collection->load();
                    $items = $item_collection->getItems();
                    foreach ($items as $item) {
                        $this->item_repository->delete($item);
                    }
                }
            } else {
                $connection->rollBack();
                return false;
            }

            $model->setReturnId($data['return_id']);
            $model->setCountryCode($return_product->getCountryCode());
            $model->setUserCode($data['user_id']);
            $model->setUserName($data['user_name']);
            $model->setTotalReal(0);
            $model->setTotalMissing(0);
            $model->setTotalExtra(0);
            $model->setTotalAccepted(0);
            $model->setTotalRejected(0);

            $return_product->setTotalAccepted(0);
            $return_product->setTotalAmountAccepted(0);
            $return_product->setTotalRejected(0);

            $this->repository->save($model);

            $result = $model;
            $map_items = [];

            foreach ($data['items'] as $item_data) {
                /**
                 * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem $item
                 */
                $item = $this->item_factory->create();
                $item->setReturnId($data['return_id']);
                $item->setControlQaId($result->getId());
                $item->setSku($item_data['sku']);
                $item->setSize($item_data['size']);
                $item->setQtyReturn($item_data['qty_return']);
                $item->setQtyReal($item_data['qty_real']);
                $item->setQtyAccepted($item_data['qty_accepted']);
                $item->setQtyReject($item_data['qty_rejected']);
                $item->setQtyExtra(0);
                $item->setQtyMissing(0);

                if ($item->getQtyReal() < $item->getQtyReturn()) {
                    $item->setQtyMissing($item->getQtyReturn() - $item->getQtyReal());
                } else if ($item->getQtyReal() > $item->getQtyReturn()) {
                    $item->setQtyExtra($item->getQtyReal() - $item->getQtyReturn());
                }

                if (is_array($item_data['reason_codes'])) {
                    $item->setReasonCodes(implode(',', $item_data['reason_codes']));
                } else {
                    $item->setReasonCodes($item_data['reason_codes']);
                }
                $item->setReasonText($item_data['text']);

                $this->log->debug('item', ['data' => $item->getData()]);

                $this->item_repository->save($item);

                $key = $item->getSku() . '.' . $item->getSize();

                $map_items[$key] = [
                    'sku' => $item->getSku(),
                    'size' => $item->getSize(),
                    'qty_real' => $item->getQtyReal(),
                    'qty_extra' => $item->getQtyExtra(),
                    'qty_missing' => $item->getQtyMissing(),
                    'qty_reject' => $item->getQtyReject()
                ];

                $model->setTotalReal($model->getTotalReal() + $item->getQtyReal());
                $model->setTotalExtra($model->getTotalExtra() + $item->getQtyExtra());
                $model->setTotalMissing($model->getTotalMissing() + $item->getQtyMissing());
                $model->setTotalAccepted($model->getTotalAccepted() + $item->getQtyAccepted());
                $model->setTotalRejected($model->getTotalRejected() + $item->getQtyReject());
            }

            foreach ($map_items as $map_item) {
                $ids = $this->return_product_item_repository->findByReturnIdAndGetIds(
                    $model->getReturnId(),
                    $map_item['sku'],
                    $map_item['size']
                );

                $last_index = count($ids) - 1;

                for ($i = 0; $i < count($ids); $i++) {
                    $id = $ids[$i];
                    $last = ($i == $last_index);

                    $update_result = $this->return_product_item_repository->updateByControlQa($id, $map_item, $last);

                    if (is_null($update_result)) {
                        $error = true;
                        break;
                    }

                    $item = $update_result['item'];
                    $map_item = $update_result['map_item'];

                    $return_product->setTotalAccepted($return_product->getTotalAccepted() + $item->getQtyAccepted());
                    $return_product->setTotalAmountAccepted($return_product->getTotalAmountAccepted() + $item->getAmountAccepted());
                    $return_product->setTotalRejected($return_product->getTotalRejected() + $item->getQtyRejected());
                }
            }

            if ($error) {
                $connection->rollBack();
                return false;
            } else {
                $this->repository->save($model);
                $this->return_product_repository->markAsControlQa($return_product);

                $connection->commit();
                return $result;
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param $id
     * @param $value
     * @return AbstractCollection
     */
    private function findByAttributeName($name, $value)
    {
        $collection = $this->collectionFactory->create();
        return $collection->addFieldToFilter($name, ['eq' => $value]);
    }
}
