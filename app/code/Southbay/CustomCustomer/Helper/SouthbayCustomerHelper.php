<?php

namespace Southbay\CustomCustomer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Southbay\CustomCustomer\Api\Data\CustomerConfigInterface;
use Southbay\CustomCustomer\Api\Data\SoldToInterface;
use Magento\Directory\Model\CountryFactory;

class SouthbayCustomerHelper extends AbstractHelper
{
    private $customerConfigRepository;
    private $soldToRepository;

    private $countryFactory;

    public function __construct(Context                                                 $context,
                                \Southbay\CustomCustomer\Model\CustomerConfigRepository $customerConfigRepository,
                                CountryFactory                                          $countryFactory,
                                \Southbay\CustomCustomer\Model\SoldToRepository         $soldToRepository)
    {
        $this->customerConfigRepository = $customerConfigRepository;
        $this->soldToRepository = $soldToRepository;
        $this->countryFactory = $countryFactory;
        parent::__construct($context);
    }

    /**
     * @param $email
     * @return CustomerConfigInterface|null
     */
    public function getConfigByEmail($email)
    {
        return $this->customerConfigRepository->findByCustomerEmail($email);
    }

    /**
     * @param $email
     * @param $sold_to_id
     * @return SoldToInterface|null
     */
    public function getSoldToById($email, $sold_to_id)
    {
        $config = $this->getConfigByEmail($email);

        if (is_null($config)) {
            return null;
        }

        $list = $this->getSoldToList($config, $sold_to_id);

        if (empty($list)) {
            return null;
        }

        return $list[0];
    }

    /**
     * @param CustomerConfigInterface $config
     * @return mixed
     */
    public function getCountriesAvailable($config)
    {
        return $this->_getCodes($config);
    }

    public function getSoldToIds($config)
    {
        return $this->_getCodes($config->getSoldToIds());
    }

    public function getSoldToCodes($config)
    {
        $list = $this->getSoldToList($config);

        if (empty($list)) {
            return [];
        }

        $result = [];

        foreach ($list as $item) {
            $result[] = $item->getCustomerCode();
        }

        return $result;
    }

    /**
     * @param CustomerConfigInterface $config
     * @return mixed
     */
    public function getSoldToList($config, $sold_to_id = null)
    {
        $ids = $this->getSoldToIds($config);

        if (empty($ids)) {
            return [];
        }

        if (!is_null($sold_to_id)) {
            if (in_array($sold_to_id, $ids)) {
                $ids = [$sold_to_id];
            } else {
                return [];
            }
        }

        $result = [];

        foreach ($ids as $id) {
            $item = $this->soldToRepository->getById($id);
            if (!is_null($item) && $item->getId()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function _getCodes($values)
    {
        $result = [];

        if (!empty($values)) {
            $codes = explode(',', $values);
            $this->_logger->debug('_getCodes', ['c' => $codes]);
            if (!empty($codes)) {
                $result = $codes;
            }
        }

        return $result;
    }

    public function soldToListToOptions($list)
    {
        $result = [];
        $countries_map = [];

        /**
         * @var SoldToInterface $item
         */
        foreach ($list as $item) {

            if (isset($countries_map[$item->getCountryCode()])) {
                $country = $countries_map[$item->getCountryCode()];
            } else {
                /**
                 * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
                 */
                $collection = $this->countryFactory->create()->getCollection();
                $collection->addFieldToFilter('country_id', ['eq' => $item->getCountryCode()]);
                $collection->load();

                $country = $collection->getFirstItem();
                $countries_map[$item->getCountryCode()] = $country;
            }

            $result[] = [
                'value' => $item->getId(),
                'label' => $item->getCustomerCode() . '-' . $item->getCustomerName() . ' (' . $country->getName() . ')'
            ];
        }

        return $result;
    }
}
