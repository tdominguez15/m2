<?php

declare(strict_types=1);

namespace Southbay\CustomCustomer\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Southbay\CustomCustomer\Api\Data\ShipToInterface;
use Southbay\CustomCustomer\Api\ShipToRepositoryInterface;
use Southbay\CustomCustomer\Model\ResourceModel\ShipTo\CollectionFactory;
use Southbay\CustomCustomer\Model\ShipToFactory;

/**
 * Class ShipToRepository
 * @package Southbay\CustomCustomer\Model
 */
class ShipToRepository implements ShipToRepositoryInterface
{
    /**
     * @var ShipToFactory
     */
    protected $shipToFactory;

    /**
     * @var CollectionFactory
     */
    protected $shipToCollectionFactory;

    /**
     * ShipToRepository constructor.
     * @param ShipToFactory $shipToFactory
     * @param CollectionFactory $shipToCollectionFactory
     */
    public function __construct(
        ShipToFactory $shipToFactory,
        CollectionFactory $shipToCollectionFactory
    ) {
        $this->shipToFactory = $shipToFactory;
        $this->shipToCollectionFactory = $shipToCollectionFactory;
    }

    /**
     * Get ship to by ID.
     *
     * @param int $id
     * @return \Southbay\CustomCustomer\Model\ShipTo
     */
    public function getById($id)
    {
        $shipTo = $this->shipToFactory->create();
        $shipTo->load($id);
        return $shipTo;
    }

    /**
     * Save ship to.
     *
     * @param \Southbay\CustomCustomer\Api\Data\ShipToInterface $shipTo
     * @return \Southbay\CustomCustomer\Api\Data\ShipToInterface
     */
    public function save(ShipToInterface $shipTo)
    {
        $shipTo->save();
        return $shipTo;
    }

    /**
     * Delete ship to.
     *
     * @param \Southbay\CustomCustomer\Api\Data\ShipToInterface $shipTo
     * @return void
     */
    public function delete(ShipToInterface $shipTo)
    {
        $shipTo->delete();
    }

    /**
     * Get ship to by customer code.
     *
     * @param string $customerCode
     * @return \Southbay\CustomCustomer\Model\ShipTo[]
     */
    public function getByCustomerCode(string $customerCode): array
    {
        $shipToCollection = $this->shipToCollectionFactory->create();
        $shipToCollection->addFieldToFilter('southbay_ship_to_customer_code', $customerCode);
        $shipToCollection->addFieldToFilter('southbay_ship_to_is_active', 1);
        $shipToData = [];
        foreach ($shipToCollection as $shipTo) {
            $shipToData[] = $shipTo;
        }

        return $shipToData;
    }
    public function createOrUpdate($data)
    {
        $model = null;

        if (empty($data['southbay_ship_to_id'])) {
            $model = $this->getById($data['southbay_ship_to_id']);
        }


        if ($model === null) {
            $model = $this->shipToFactory->create();
        }

        foreach ($data as $key => $value) {
            $model->setData($key, $value);
        }

        try {
            $this->save($model);
        } catch (CouldNotSaveException $e) {
            throw $e;
        }

        return $model;

    }
}
