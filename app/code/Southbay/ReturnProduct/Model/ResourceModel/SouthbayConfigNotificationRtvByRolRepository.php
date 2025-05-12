<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\App\State;

class SouthbayConfigNotificationRtvByRolRepository
{
    private $log;
    private $collectionFactory;
    private $repository;
    private $factory;

    private $helper;
    private $rolesCollectionFactory;

    private $usersCollectionFactory;

    private $sendEmailNotification;

    private $state;

    private $_approval_users_cache = [];

    private $approvalUsersFactory;

    public function __construct(
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayConfigNotificationRtvByRolCollectionFactory $collectionFactory,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayConfigNotificationRtvByRol                             $repository,
        \Southbay\ReturnProduct\Model\SouthbayConfigNotificationRtvByRolFactory                                    $factory,
        \Southbay\ReturnProduct\Helper\Data                                                                        $helper,
        \Southbay\ReturnProduct\Helper\SendEmailNotification                                                       $sendEmailNotification,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory                                          $rolesCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory                                                   $usersCollectionFactory,
        \Southbay\ReturnProduct\Model\SouthbayReturnFinancialApprovalUsersFactory                                  $approvalUsersFactory,
        State                                                                                                      $state,
        \Psr\Log\LoggerInterface                                                                                   $log
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->helper = $helper;
        $this->log = $log;
        $this->rolesCollectionFactory = $rolesCollectionFactory;
        $this->usersCollectionFactory = $usersCollectionFactory;
        $this->sendEmailNotification = $sendEmailNotification;
        $this->approvalUsersFactory = $approvalUsersFactory;
        $this->state = $state;
    }

    /**
     * @param $data
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol
     */
    public function findOrNew($data)
    {
        if (empty($data)) {
            return $this->factory->create();
        } else {
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_COUNTRY_CODE, $data['country']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_TYPE, $data['type']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_TYPE_ROL, $data['type_rol']);
            $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_STATUS, $data['status']);

            return $collection->getFirstItem();
        }
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol $model
     */
    public function save($model)
    {
        $this->repository->save($model);

        return $model;
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @param string $country_code
     * @return void
     */
    public function sendNotification($model, $country_code, $ready_for_approval)
    {
        $this->log->debug('sendNotification', ['return_id' => $model->getId(), 'country' => $country_code, 'r' => $ready_for_approval]);

        if ($ready_for_approval) {
            $exchange = $this->helper->getLastExchange($country_code);

            if (is_null($exchange)) {
                $this->log->debug('sendNotification. Not exchange');
                return;
            }

            $result = $this->listByMaxAmount($model, $exchange->getExchange(), $country_code);

            if (!empty($result)) {
                $this->log->debug('sendNotification, ready for send');

                try {
                    $this->state->setAreaCode('adminhtml');
                } catch (\Exception $e) {
                }

                /**
                 * @var \Magento\Framework\App\ObjectManager $objectManager
                 */
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                /**
                 * @var \Magento\Backend\App\Action\Context $context
                 */
                $context = $objectManager->get('Magento\Backend\App\Action\Context');
                $url = $context->getBackendUrl()->getUrl('southbay_return_product/approval/new', ['id' => $model->getId()]);
                $this->_sendNotification($result, $model, ['url_approval' => $url]);
            } else {
                $this->log->debug('sendNotification. Not listByMaxAmount');
            }
        }

        $configs = $this->getConfigByType($model->getType(), $model->getStatus(), $country_code);

        if (!is_null($configs)) {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol $config
             */
            foreach ($configs as $config) {
                $roles_config = $this->helper->getAllConfigByTypeRol($config->getTypeRol(), $country_code);
                if (count($roles_config) > 0) {
                    /**
                     * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $role_config
                     */
                    foreach ($roles_config as $role_config) {
                        $emails = $this->getEmailsByRoleCode($role_config->getRolCode());
                        if (!empty($emails)) {
                            $this->_sendNotification(['config' => $config, 'emails' => $emails], $model);
                        }
                    }
                }
            }
        }
    }

    private function _sendNotification($data, $model, $extra_vars = [])
    {
        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol $config
         */
        $config = $data['config'];
        $emails = $data['emails'];

        foreach ($emails as $_to) {
            $this->sendEmailNotification->sendNotification($config->getTemplateCode(), $_to['email'], $_to['name'], $model, $extra_vars);
        }
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @param float $exchange
     * @param string $country_code
     * @return mixed
     */
    private function listByMaxAmount($model, $exchange, $country_code)
    {
        $config = $this->getConfigByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL,
            $model->getType(), $model->getStatus(), $country_code);

        if (is_null($config)) {
            $this->log->debug('listByMaxAmount. not config');
            return [];
        }

        $amount_to_approve = round($model->getTotalAmount() / $exchange, 3);

        $configApprovals = $this->helper->getRolForApprovalByMaxAmount($amount_to_approve, $model->getType(), $country_code);

        if (is_null($configApprovals)) {
            $this->log->debug('listByMaxAmount. not configApproval');
            return [];
        }

        $emails = [];

        foreach ($configApprovals as $configApproval) {
            $_emails = $this->getEmailsByRoleCode($configApproval->getRolCode());
            if (is_null($_emails)) {
                $this->log->debug('listByMaxAmount. not emails: ', ['config_approval' => $configApproval->getData()]);
            } else {
                $emails = array_merge($_emails, $emails);
                $this->saveUsersForApprovals($_emails, $configApproval->getRolCode(), $model->getId());
            }
        }

        if (empty($emails)) {
            return [];
        }

        return [
            'config' => $config,
            'emails' => $emails
        ];
    }

    private function saveUsersForApprovals($users, $role_code, $return_product_id)
    {
        foreach ($users as $user) {
            $key = $user['user_id'] . '-' . $return_product_id;
            if (isset($this->_approval_users_cache[$key])) {
                continue;
            }

            /**
             * @var \Southbay\ReturnProduct\Model\SouthbayReturnFinancialApprovalUsers $approval_user
             */
            $approval_user = $this->approvalUsersFactory->create();
            $approval_user->setUserCode($user['user_id']);
            $approval_user->setUserName($user['username']);
            $approval_user->setRolCode($role_code);
            $approval_user->setReturnId($return_product_id);
            $approval_user->save();

            $this->_approval_users_cache[$key] = $user;
        }
    }

    private function getEmailsByRoleCode($role_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $roles_collection = $this->rolesCollectionFactory->create();
        /**
         * @var \Magento\Authorization\Model\Role $role
         */
        $role = $roles_collection->getItemById($role_code);
        if (is_null($role)) {
            $this->log->debug('getEmailsByRoleCode. sin role');
            return null;
        }

        $ids = $role->getRoleUsers();

        if (empty($ids)) {
            $this->log->debug('getEmailsByRoleCode. sin ids');
            return null;
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $users_collection = $this->usersCollectionFactory->create();
        $users_collection->addFieldToFilter('user_id', ['in' => $ids]);
        $users_collection->addFieldToFilter('is_active', 1);
        $users_collection->load();

        if ($users_collection->count() == 0) {
            $this->log->debug('getEmailsByRoleCode. sin users_collection');
            return null;
        }

        $items = $users_collection->getItems();
        $result = [];

        /**
         * @var \Magento\User\Model\User $item
         */
        foreach ($items as $item) {
            $result[] = [
                'email' => $item->getEmail(),
                'user_id' => $item->getId(),
                'username' => $item->getUserName(),
                'name' => $item->getName()
            ];
        }

        $this->log->debug('getEmailsByRoleCode.', ['rol' => $role->getRoleName(), 'r' => $result]);

        return $result;
    }

    /**
     * @param string $type_rol
     * @param string $type
     * @param string $country_code
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol|null
     */
    private function getConfigByTypeRol($type_rol, $type, $status, $country_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_TYPE_ROL, $type_rol);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_TYPE, $type);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_STATUS, $status);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_COUNTRY_CODE, $country_code);

        $collection->load();

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getFirstItem();
    }

    /**
     * @param string $type
     * @param string $country_code
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol|null
     */
    private function getConfigByType($type, $status, $country_code)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_TYPE, $type);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_STATUS, $status);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_COUNTRY_CODE, $country_code);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol::ENTITY_TYPE_ROL,
            [
                'neq' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL
            ]);

        $collection->load();

        if ($collection->count() == 0) {
            return null;
        }

        return $collection->getItems();
    }
}
