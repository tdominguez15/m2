<?php
namespace Southbay\CustomCustomer\Api;

use Southbay\CustomCustomer\Api\Data\MapCountryInterface;

/**
 * Interface MapCountryRepositoryInterface
 * @package Southbay\MapCountry\Api
 */
interface MapCountryRepositoryInterface
{
    /**
     * Get map country by ID.
     *
     * @param int $id
     * @return \Southbay\CustomCustomer\Api\Data\MapCountryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save map country.
     *
     * @param \Southbay\CustomCustomer\Api\Data\MapCountryInterface $mapCountry
     * @return \Southbay\CustomCustomer\Api\Data\MapCountryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(MapCountryInterface $mapCountry);

    /**
     * Delete map country.
     *
     * @param \Southbay\CustomCustomer\Api\Data\MapCountryInterface $mapCountry
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(MapCountryInterface $mapCountry);
}
