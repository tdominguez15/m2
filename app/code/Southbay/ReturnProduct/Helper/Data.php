<?php

namespace Southbay\ReturnProduct\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    private $reasonReturnCollectionFactory;
    private $reasonRejectCollectionFactory;
    private $configCollectionFactory;

    private $exchangeReturnRepository;

    private $authSession;
    private $configRtvRepository;
    private $mapCountryRepository;

    public function __construct(Context                                                                                      $context,
                                \Southbay\CustomCustomer\Model\MapCountryRepository                                          $mapCountryRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnConfigCollectionFactory $configCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayRolConfigRtvRepository                   $configRtvRepository,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReasonReturnCollectionFactory $reasonReturnCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReasonRejectCollectionFactory $reasonRejectCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayExchangeReturnRepository                 $exchangeReturnRepository,
                                Session                                                                                      $authSession)
    {
        $this->configCollectionFactory = $configCollectionFactory;
        $this->reasonReturnCollectionFactory = $reasonReturnCollectionFactory;
        $this->reasonRejectCollectionFactory = $reasonRejectCollectionFactory;
        $this->exchangeReturnRepository = $exchangeReturnRepository;
        $this->configRtvRepository = $configRtvRepository;
        $this->authSession = $authSession;
        $this->mapCountryRepository = $mapCountryRepository;
        parent::__construct($context);
    }

    public function getCountriesMap()
    {
        return $this->mapCountryRepository->toMap();
    }

    public function getSapCountriesMap()
    {
        return $this->mapCountryRepository->toSapMap();
    }

    public function getAllConfig()
    {
        $roles = $this->authSession->getUser()->getRoles();

        $list = $this->configRtvRepository->getAll();

        $result = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $item
         */
        foreach ($list as $item) {
            if (in_array($item->getRolCode(), $roles)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getTypeReturnByTypeRol($type_rol)
    {
        $list = $this->getAllConfig();

        $result = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $item
         */
        foreach ($list as $item) {
            if ($item->getTypeRol() == $type_rol) {
                if (!in_array($item->getType(), $result)) {
                    $result[] = $item->getType();
                }
            }
        }

        return $result;
    }

    public function getSapCountriesByTypeRol($type_rol)
    {
        $countries = $this->getCountriesByTypeRol($type_rol);

        if (empty($countries)) {
            return [];
        }

        $map = $this->getCountriesMap();
        $result = [];

        foreach ($countries as $country) {
            if (isset($map[$country])) {
                $result[] = $map[$country];
            }
        }

        return $result;
    }

    public function getCountriesByTypeRol($type_rol)
    {
        $list = $this->getAllConfig();

        $result = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $item
         */
        foreach ($list as $item) {
            if ($item->getTypeRol() == $type_rol) {
                if (!in_array($item->getCountryCode(), $result)) {
                    $result[] = $item->getCountryCode();
                }
            }
        }

        return $result;
    }

    public function getAllConfigByTypeRol($type_rol, $country_code)
    {
        return $this->configRtvRepository->getRolesByReturnTypeRol($type_rol, $country_code);
    }

    public function getMaxApprovalAmount($country_code)
    {
        $config_roles = $this->configRtvRepository->getApprovalRolesByCountyCode($country_code);

        if (empty($config_roles)) {
            return 0;
        }

        $map = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $config
         */
        foreach ($config_roles as $config) {
            $map[$config->getRolCode()] = $config;
        }

        $roles = $this->authSession->getUser()->getRoles();

        $max_amount = 0;
        $multiple_approvals = false;

        foreach ($roles as $code) {
            if (isset($map[$code])) {
                $config = $map[$code];
                if ($config->getApprovalUseAmountLimit()) {
                    if ($max_amount < $config->getApprovalAmountLimit()) {
                        $max_amount = $config->getApprovalAmountLimit();
                        $multiple_approvals = $config->getRequireAllMembers();
                    }
                } else {
                    $multiple_approvals = $config->getRequireAllMembers();
                    $max_amount = null;
                    break;
                }
            }
        }

        return ['max_amount' => $max_amount, 'multiple_approvals' => $multiple_approvals];
    }

    /**
     * @param $return_type
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig|null
     */
    public function getConfig($return_type, $country)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_TYPE, ['eq' => $return_type]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig::ENTITY_COUNTRY_CODE, ['eq' => $country]);

        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        } else {
            return null;
        }
    }

    /**
     * @param $country
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn|null
     */
    public function getLastExchange($country)
    {
        return $this->exchangeReturnRepository->getLastExchange($country);
    }

    public function getApprovalPendingItem($item, $type, $exchange)
    {
        if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT) {
            $_detail = [
                'total_accepted' => $item->getTotalReturn(),
                'total_amount' => $item->getTotalAmount(),
                'total_amount_financial' => round(($item->getTotalAmount() / $exchange), 3),
                'exchange_rate' => $exchange
            ];
        } else {
            $_detail = [
                'total_accepted' => $item->getTotalAccepted(),
                'total_amount' => $item->getTotalAmountAccepted(),
                'total_amount_financial' => round(($item->getTotalAmountAccepted() / $exchange), 3),
                'exchange_rate' => $exchange
            ];
        }

        return [
            'id' => $item->getId(),
            'type' => $type,
            'customer' => $item->getCustomerName(),
            'detail' => $_detail
        ];
    }

    public function getReasonReturn($map = false)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->reasonReturnCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn::ENTITY_COUNTRY_CODE, ['eq' => 'AR']);
        $collection->setOrder(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn::ENTITY_NAME, 'ASC');
        $collection->load();

        return $this->_getReasons($collection, $map);
    }

    public function getReasonReject($map = false)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->reasonRejectCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReject::ENTITY_COUNTRY_CODE, ['eq' => 'AR']);
        $collection->setOrder(\Southbay\ReturnProduct\Api\Data\SouthbayReasonReject::ENTITY_NAME, 'ASC');
        $collection->load();

        return $this->_getReasons($collection, $map);
    }

    private function _getReasons($collection, $map)
    {
        $result = [];
        $items = $collection->getItems();

        foreach ($items as $item) {
            if ($map) {
                $result[$item->getId()] = $item->getName();
            } else {
                $result[] = [
                    'code' => $item->getId(),
                    'name' => $item->getName()
                ];
            }
        }

        return $result;
    }

    /**
     * @param float $amount
     * @param string $return_type
     * @param string $country_code
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv[]|null
     */
    public function getRolForApprovalByMaxAmount($amount, $return_type, $country_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $config_collection
         */
        $config_collection = $this->configRtvRepository->getCollection();
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_COUNTRY_CODE, $country_code);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE, $return_type);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE_ROL,
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_APPROVAL_AMOUNT_LIMIT,
            ['gt' => 0]);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_APPROVAL_AMOUNT_LIMIT,
            ['gteq' => $amount]);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_APPROVAL_USE_AMOUNT_LIMIT, true);
        $config_collection->setOrder(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_APPROVAL_AMOUNT_LIMIT, 'ASC');
        $config_collection->load();
        $result = [];

        if ($config_collection->count() > 0) {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $first
             */
            $first = $config_collection->getFirstItem();

            $items = $config_collection->getItems();

            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $item
             */
            foreach ($items as $item) {
                if ($first->getApprovalAmountLimit() == $item->getApprovalAmountLimit()) {
                    $result[] = $item;
                }
            }
        } else {
            $result = $this->getRolWithoutMaxAmount($return_type, $country_code);
        }

        return $this->filterRolForApproval($result);
    }

    private function filterRolForApproval($items)
    {
        if (!$items) {
            return $items;
        }

        $result = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $item
         */
        foreach ($items as $item) {
            if ($item->getRequireAllMembers()) {
                return [$item];
            }

            $result[] = $item;
        }

        return $result;
    }

    public function getRolWithoutMaxAmount($return_type, $country_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $config_collection
         */
        $config_collection = $this->configRtvRepository->getCollection();
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_COUNTRY_CODE, $country_code);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE, $return_type);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE_ROL,
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);
        $config_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_APPROVAL_USE_AMOUNT_LIMIT, 0);
        $config_collection->load();

        if ($config_collection->count() > 0) {
            return $config_collection->getItems();
        } else {
            return null;
        }
    }
}
