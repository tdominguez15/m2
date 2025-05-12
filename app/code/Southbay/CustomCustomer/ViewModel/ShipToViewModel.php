<?php

declare(strict_types=1);

namespace Southbay\CustomCustomer\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Southbay\CustomCustomer\Model\ShipToRepository;


class ShipToViewModel implements ArgumentInterface
{
    /**
     * @var ShipToRepository
     */
    protected $shipToRepository;

    /**
     * ShipToViewModel constructor.
     * @param ShipToRepository $shipToRepository
     */
    public function __construct(
        ShipToRepository $shipToRepository
    ) {
        $this->shipToRepository = $shipToRepository;
    }

    /**
     * Get ShipTo by ID.
     *
     * @param int $shipToId
     * @return string|null
     */
    public function getShipToCodeById(int $shipToId)
    {
        try {
            return $this->shipToRepository->getById($shipToId)->getCode();
        } catch (\Exception $e) {
            return '';
        }
    }
}
