<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory;

use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;

class Repository
{
    private $collectionFactory;
    private $repository;

    public function __construct(\Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory\CollectionFactory $collectionFactory,
                                \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory                   $repository)
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
    }

    public function retry($id)
    {
        /**
         * @var \Southbay\Product\Model\SouthbayProductImportHistory $item
         */
        $item = $this->collectionFactory->create()->getItemById($id);

        if ($item) {
            if ($item->getStatus() != SouthbayProductImportHistoryInterface::STATUS_INIT) {
                $item->setStatus(SouthbayProductImportHistoryInterface::STATUS_INIT);
                $item->setStartImportDate(null);
                $item->setendImportDate(null);
                $item->setResultMsg('');
                $item->setLines(0);
                $this->repository->save($item);
            } else {
                throw new \Exception(__('La carga de linea está en un estado el cual no permite reintentar'));
            }
        } else {
            throw new \Exception(__('No existe el registro que intenta modificar'));
        }

        return $item;
    }
}
