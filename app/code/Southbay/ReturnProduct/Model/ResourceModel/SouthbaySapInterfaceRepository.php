<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapInterfaceCollection;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapInterfaceCollectionFactory;

class SouthbaySapInterfaceRepository
{
    private $collectionFactory;

    public function __construct(SouthbaySapInterfaceCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $id
     * @return mixed|null
     */
    public function findByReturnProductId($id)
    {
        /**
         * @var SouthbaySapInterfaceCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_REF, $id);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_FROM, 'rtv');
        $collection->setOrder(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_CREATED_AT, 'ASC');

        if ($collection->count() == 0) {
            return null;
        }

        $items = $collection->getItems();

        $result = [
            'first' => null,
            'total' => count($items),
            'success' => 0
        ];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface $item
         */
        foreach ($items as $item) {
            if (is_null($result['first'])) {
                $result['first'] = $item;
            }

            if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_SUCCESS) {
                $result['success']++;
            }
        }

        return $result;
    }
//TODO SE DESARROLLA COMO REEMPLAZO DE LA FUNCION ANTERIOR, EN CASO DE NO HABER PROBLEMA SE PUEDE BORRAR findByReturnProductId
    /**
     * @param $id
     * @return array|null
     */
    public function findLastByReturnProductId($id)
    {
        /**
         * @var SouthbaySapInterfaceCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_REF, $id);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_FROM, 'rtv');
        $collection->setOrder(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_CREATED_AT, 'DESC');

        if ($collection->count() == 0) {
            return null;
        }

        $items = $collection->getItems();

        $result = [
            'first' => null,
            'total' => count($items),
            'success' => 0
        ];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface $item
         */
        foreach ($items as $item) {
            if (is_null($result['first'])) {
                $result['first'] = $item;  // es el ultimo debido a que esta en forma decreciente, pero se mantiene por compatibilidad anterior
            }

            if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_SUCCESS) {
                $result['success']++;
            }
        }

        return $result;
    }
}
