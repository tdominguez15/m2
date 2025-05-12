<?php
namespace Southbay\CustomCustomer\Api;

use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;

/**
 * Interface ConfigStoreRepositoryInterface
 * @api
 */
interface ConfigStoreRepositoryInterface
{
    const SOUTHBAY_STORE_CODE = 'southbay_store_code';
    const SOUTHBAY_FUNCTION_CODE = 'southbay_function_code';
    const SOUTHBAY_COUNTRY_CODE = 'southbay_country_code';
    const SOUTHBAY_AT_ONCE = 'at_once';
    const SOUTHBAY_FUTURES = 'futures';
    const SOUTHBAY_RTV = 'rtv';

    /**
     * Get config by ID
     *
     * @param int $id
     * @return \Southbay\CustomCustomer\Api\Data\ConfigStoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save config
     *
     * @param \Southbay\CustomCustomer\Api\Data\ConfigStoreInterface $config
     * @return \Southbay\CustomCustomer\Api\Data\ConfigStoreInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(ConfigStoreInterface $config);

    /**
     * Delete config
     *
     * @param \Southbay\CustomCustomer\Api\Data\ConfigStoreInterface $config
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(ConfigStoreInterface $config);
}
