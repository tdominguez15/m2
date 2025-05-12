<?php

namespace Southbay\ReturnProduct\Model\ResourceModel;

use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Southbay\CustomCustomer\Helper\SouthbayCustomerHelper;
use Southbay\ReturnProduct\Model\SouthbayReturnFinancialApproval;

class SouthbayReturnProductRepository
{
    private $log;
    private $cache;
    private $repository;
    private $collectionFactory;
    private $searchCriteriaBuilder;
    private $filterBuilder;
    private $filterGroupBuilder;
    private $collectionProcessor;
    private $helper;

    private $repository_financial_approval;
    private $financialApprovalCollectionFactory;
    private $ignore_cache = false;

    private $helper_notification;

    private $customerHelper;

    private $sapInterfaceCollectionFactory;
    private $sapDocCollectionFactory;

    private $southbayReturnFinancialApprovalUsersRepository;

    private $itemRepository;

    public function __construct(\Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProduct                                       $repository,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApproval                             $repository_financial_approval,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnFinancialApprovalCollectionFactory $financialApprovalCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnProductCollectionFactory           $collectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapInterfaceCollectionFactory            $sapInterfaceCollectionFactory,
                                \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbaySapDocCollectionFactory                  $sapDocCollectionFactory,
                                FilterBuilder                                                                                           $filterBuilder,
                                FilterGroupBuilder                                                                                      $filterGroupBuilder,
                                SearchCriteriaBuilder                                                                                   $searchCriteriaBuilder,
                                CollectionProcessorInterface                                                                            $collectionProcessor,
                                \Magento\Framework\Model\Context                                                                        $context,
                                \Southbay\ReturnProduct\Helper\Data                                                                     $helper,
                                \Southbay\ReturnProduct\Helper\SendNotification                                                         $helper_notification,
                                SouthbayCustomerHelper                                                                                  $customerHelper,
                                SouthbayReturnFinancialApprovalUsersRepository                                                          $southbayReturnFinancialApprovalUsersRepository,
                                SouthbayReturnProductItemRepository                                                                     $itemRepository,
                                \Psr\Log\LoggerInterface                                                                                $log)
    {
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->log = $log;
        $this->cache = $context->getCacheManager();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->repository_financial_approval = $repository_financial_approval;
        $this->financialApprovalCollectionFactory = $financialApprovalCollectionFactory;
        $this->helper = $helper;
        $this->helper_notification = $helper_notification;
        $this->customerHelper = $customerHelper;
        $this->sapInterfaceCollectionFactory = $sapInterfaceCollectionFactory;
        $this->sapDocCollectionFactory = $sapDocCollectionFactory;
        $this->southbayReturnFinancialApprovalUsersRepository = $southbayReturnFinancialApprovalUsersRepository;
        $this->itemRepository = $itemRepository;
    }

    private function sapInterfaceCollection($id)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->sapInterfaceCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_REF, ['eq' => $id]);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_FROM, ['eq' => 'rtv']);

        return $collection;
    }

    public function getSapInterfaceDetail($id)
    {
        $collection = $this->sapInterfaceCollection($id);
        $collection->load();

        $result = [];
        $items = $collection->getItems();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface $item
         */
        foreach ($items as $item) {
            $status_text = '';
            $retry = true;

            if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_SUCCESS) {
                $status_text = __('Ok');
                $retry = false;
            } else if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_INIT) {
                $status_text = __('Listo para enviar a SAP');
                $retry = false;
            } else if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_ERROR) {
                $status_text = __('Error enviando');
            }

            $result[] = [
                'id' => $item->getId(),
                'status' => $status_text,
                'request' => $item->getRequest(),
                'response' => $item->getResponse(),
                'retry' => $retry
            ];
        }

        return $result;
    }

    public function getTotalDocuments($id)
    {
        $result = ['total' => 0, 'total_success' => 0];
        $collection = $this->sapInterfaceCollection($id);
        $collection->load();

        if ($collection->count() == 0) {
            return $result;
        }

        $items = $collection->getItems();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface $item
         */
        foreach ($items as $item) {
            $result['total']++;
            if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::STATUS_SUCCESS) {
                $result['total_success']++;
            }
        }

        return $result;
    }

    /**
     * @param $id
     * @return AbstractCollection|null
     */
    private function _sapDocs($id)
    {
        $collection = $this->sapInterfaceCollection($id);

        if ($collection->count() > 0) {
            $ids = $collection->getAllIds();
            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $doc_collection
             */
            $doc_collection = $this->sapDocCollectionFactory->create();
            $doc_collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbaySapDoc::ENTITY_SAP_INTERFACE_ID, ['in' => $ids]);

            return $doc_collection;
        }

        return null;
    }

    public function getSapDocs($id)
    {
        $collection = $this->_sapDocs($id);
        if (is_null($collection)) {
            return [];
        }

        $result = [];
        $items = $collection->getItems();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbaySapDoc $item
         */
        foreach ($items as $item) {
            $result[] = [
                'internal_number' => $item->getDocInternalNumber(),
                'legal_number' => $item->getDocLegalNumber(),
                'total' => $item->getTotalAmount(),
                'net_total' => $item->getTotalNetAmount()
            ];
        }

        return $result;
    }

    public function hasSapDoc($id)
    {
        $collection = $this->_sapDocs($id);

        if (!is_null($collection)) {
            return ($collection->count() > 0);
        }

        return false;
    }

    /**
     * @return AbstractCollection
     */
    public function getDashboardCollection($only_checker = false)
    {
        /**
         * @var AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $configs = $this->helper->getAllConfig();

        $this->addJoinsToCollection($collection);
        if (empty($configs)) {
            $collection->addFieldToFilter('main_table.' . \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_ID, ['eq' => 0]);
            return $collection;
        }

        $map = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv $config
         */
        foreach ($configs as $config) {
            $key = $config->getCountryCode() . '-' . $config->getTypeRol();

            if (!isset($map[$key])) {
                if ($only_checker && $config->getTypeRol() != \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK) {
                    continue;
                }

                $map[$key] = [
                    'country_code' => $config->getCountryCode(),
                    'type_rol' => $config->getTypeRol()
                ];
            }
        }

        if (empty($map)) {
            $collection->addFieldToFilter('main_table.' . \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_ID, ['eq' => 0]);
            return $collection;
        }

        $country_field_name = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_COUNTRY_CODE;
        // $status_field_name = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS;

        $countries = [];

        foreach ($map as $item) {
            if (!in_array($item['country_code'], $countries)) {
                $countries[] = $item['country_code'];
            }

            /*
            if ($item['type_rol'] == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_RECEPTION) {
                $status =
                    $collection->getConnection()->quote(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_FAIL_INIT) . ',' .
                    $collection->getConnection()->quote(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD);

                $country_code = $collection->getConnection()->quote($item['country_code']);

                // $collection->getSelect()->orWhere("$country_field_name = $country_code AND $status_field_name IN ($status)");
                $collection->getSelect()->where("$country_field_name = $country_code");
            } else if ($item['type_rol'] == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL) {
                $status =
                    $collection->getConnection()->quote(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA) . ',' .
                    $collection->getConnection()->quote(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT);

                $country_code = $collection->getConnection()->quote($item['country_code']);

                // $collection->getSelect()->orWhere("$country_field_name = $country_code AND $status_field_name IN ($status)");
                $collection->getSelect()->where("$country_field_name = $country_code");
            } else if ($item['type_rol'] == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CONTROL_QA) {
                $status =
                    $collection->getConnection()->quote(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_RECEIVED);

                $country_code = $collection->getConnection()->quote($item['country_code']);

                // $collection->getSelect()->orWhere("$country_field_name = $country_code AND $status_field_name IN ($status)");
                $collection->getSelect()->where("$country_field_name = $country_code");
            } else if ($item['type_rol'] == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK) {
                $country_code = $collection->getConnection()->quote($item['country_code']);

                $collection->getSelect()->where("$country_field_name = $country_code");
            }
            */
        }

        $collection->addFieldToFilter($country_field_name, ['in' => $countries]);

        return $collection;
    }

    public function getAllRecent($customer_email)
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0, 0);
        $date->modify('-3 months');

        $collection = $this->getAll($customer_email);

        if (is_null($collection)) {
            return null;
        }

        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CREATED_AT, ['gteq' => $date->format('Y-m-d')]);

        return $collection;
    }

    public function getAll($customer_email)
    {
        $config = $this->customerHelper->getConfigByEmail($customer_email);

        if (is_null($config)) {
            return null;
        }

        $sold_to_codes = $this->customerHelper->getSoldToCodes($config);

        if (empty($sold_to_codes)) {
            return null;
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CUSTOMER_CODE, ['in' => $sold_to_codes]);
        $collection->addOrder(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CREATED_AT, 'DESC');
        return $collection;
    }

    /**
     * @param $id
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct|null
     */
    public function findById($id)
    {
        $identifier = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::CACHE_TAG . '_' . $id;
        $item = $this->cache->load($identifier);

        if (!$item) {
            $collection = $this->collectionFactory->create();
            $item = $collection->getItemById($id);

            if (!is_null($item)) {
                $this->cache->save(serialize($item), $identifier, [\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::CACHE_TAG]);
            }
        } else {
            $item = unserialize($item);
        }

        return $item;
    }

    public function markAsCancel($model)
    {


        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CANCEL);
        $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CANCEL);

        $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     */
    public function markAsControlQa($model)
    {
        $force_send_reject_notification = false;
        if ($model->getTotalAccepted() > 0) {
            if ($model->getType() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_GOOD) {
                $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA_GOOD);
                $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CONTROL_QA_GOOD);
            } else {
                $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA);
                $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CONTROL_QA);
            }

            if ($model->getTotalRejected() > 0) {
                $force_send_reject_notification = true;
            }
        } else {
            $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_REJECTED_IN_CONTROL_QA);
            $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_REJECTED_IN_CONTROL_QA);
        }

        $this->save($model, $force_send_reject_notification);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return void
     */
    public function markAsReject($model)
    {
        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_REJECTED);
        $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_REJECTED);

        $this->itemRepository->cancelReturnProduct($model->getId());

        $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return void
     */
    public function markAsConfirmed($model)
    {
        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONFIRMED);
        $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CONFIRMED);

        $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return void
     */
    public function markAsArchived($model)
    {
        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_ARCHIVED);
        $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_ARCHIVED);

        $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return void
     */
    public function markAsDocumentsSent($model)
    {
        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_DOCUMENTS_SENT);
        $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_DOCUMENTS_SENT);

        $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return void
     */
    public function markAsClosed($model)
    {
        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CLOSED);
        $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_CLOSED);

        $this->save($model);
    }

    public function getApprovalAmount($return_product, $exchange)
    {
        if ($return_product->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT) {
            return round(($return_product->getTotalAmount() / $exchange), 3);
        } else {
            return round(($return_product->getTotalAmountAccepted() / $exchange), 3);
        }
    }

    public function approval($return_product, $ok, $user_id, $user_name, $exchange, $multiple_approvals = false)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->financialApprovalCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_RETURN_ID,
            ['eq' => $return_product->getId()]);
        $collection->load();

        $new_approval = false;

        if ($multiple_approvals) {
            $total = $this->southbayReturnFinancialApprovalUsersRepository->getTotalUserByReturnId($return_product->getId());
            if ($total == 0) {
                $multiple_approvals = false;
            }
        }

        if ($collection->count() > 0) {
            if ($multiple_approvals) {
                /**
                 * @var SouthbayReturnFinancialApproval $model
                 */
                $model = $collection->getFirstItem();

                if (!$model->getApproved()) {
                    return;
                }
            } else {
                return;
            }
        } else {
            /**
             * @var SouthbayReturnFinancialApproval $model
             */
            $model = $collection->getFirstItem();
            $new_approval = true;
        }

        $approval_user = $this->southbayReturnFinancialApprovalUsersRepository->findUserByReturnId($user_id, $return_product->getId());

        if ($approval_user && $approval_user->getApproved()) {
            return;
        } else if (is_null($approval_user)) {
            $multiple_approvals = false;
        }

        $model->setCountryCode($return_product->getCountryCode());
        $model->setReturnId($return_product->getId());
        $model->setApproved($ok);
        $model->setUserCode($user_id);
        $model->setUserName($user_name);

        if ($return_product->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT) {
            $model->setTotalAccepted($return_product->getTotalReturn());
            $model->setTotalAcceptedAmount($return_product->getTotalAmount());
        } else {
            $model->setTotalAccepted($return_product->getTotalAccepted());
            $model->setTotalAcceptedAmount($return_product->getTotalAmountAccepted());
        }

        $model->setTotalValuedAmount($this->getApprovalAmount($return_product, $exchange));
        $model->setExchangeRate($exchange);

        if ($approval_user) {
            $approval_user->setApproved($ok);
            $approval_user->save();

            if ($new_approval) {
                $model->setTotalApprovals($this->southbayReturnFinancialApprovalUsersRepository->getTotalUserByReturnId($return_product->getId()));
                $model->setTotalPendingApprovals($model->getTotalApprovals() - 1);
            } else {
                $model->setTotalPendingApprovals($model->getTotalPendingApprovals() - 1);
            }

            if (!$ok) {
                $model->setTotalPendingApprovals(0);
            }
        } else {
            $model->setTotalPendingApprovals(0);
        }

        $model->setRequireAllMembers($multiple_approvals);

        $this->repository_financial_approval->save($model);

        if ($ok) {
            if ($multiple_approvals && $model->getTotalPendingApprovals() <= 0) {
                $this->markAsApproval($return_product);
            } else if (!$multiple_approvals) {
                $this->markAsApproval($return_product);
            }
        } else {
            $this->markAsReject($return_product);
        }
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return void
     */
    public function markAsApproval($model)
    {
        if ($model->getType() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::RETURN_TYPE_CODE_FAIL) {
            $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL);
            $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_APPROVAL);
        } else {
            $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD);
            $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_APPROVAL_GOOD);
        }

        $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     */
    public function markAsReceived($model)
    {
        $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_RECEIVED);
        $model->setStatusName(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_NAME_RECEIVED);

        $this->save($model);
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @param bool $force_send_reject_notification
     * @return \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct
     */
    public function save($model, $force_send_reject_notification = false)
    {
        $is_new = false;
        if (is_null($model->getCreatedAt())) {
            $is_new = true;
        }

        $this->repository->save($model);

        if ($is_new) {
            $model = $this->findById($model->getId());
        }

        $identifier = \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::CACHE_TAG . '_' . $model->getId();

        $model = $this->checkAutomaticApproval($model);

        if (!$this->ignore_cache) {
            $this->sendNotification($model, $force_send_reject_notification);
            $this->cache->save(serialize($model), $identifier, [\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::CACHE_TAG]);
        }

        return $model;
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @param bool $force_send_reject_notification
     */
    private function sendNotification($model, $force_send_reject_notification = false)
    {
        if ($model->getTotalReturn() > 0) {
            $this->helper_notification->send($model, $model->getCountryCode(), $this->availableForApproval($model), $force_send_reject_notification);
        }
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     */
    private function checkAutomaticApproval($model)
    {
        $this->log->debug('checkAutomaticApproval',
            ['id' => $model->getId(),
                'status' => $model->getStatus(),
                'pending_status' => $this->getPendingApprovalStatus()
            ]);

        if (in_array($model->getStatus(), $this->getPendingApprovalStatus())) {
            $config = $this->helper->getConfig($model->getType(), $model->getCountryCode());
            if (!is_null($config)) {
                $this->log->debug('There is general configuracion', ['config' => $config->getData()]);
                $exchange = $this->helper->getLastExchange($model->getCountryCode());
                $this->log->debug('Exchange: ', ['data' => $exchange]);

                if (!is_null($exchange)) {
                    $this->log->debug('There is exchange', ['exchange' => $exchange->getData()]);
                    $this->log->debug('check', [
                        'available_automatic_approval' => $config->getAvailableAutomaticApproval(),
                        'max_automatic_amount' => $config->getMaxAutomaticAmount(),
                        'model_total_amount' => $model->getTotalAmount()
                    ]);
                    if ($config->getAvailableAutomaticApproval()
                        && !is_null($config->getMaxAutomaticAmount())
                        && $config->getMaxAutomaticAmount() > 0
                        && $model->getTotalAmount() > 0) {
                        $approval_amount = round(($model->getTotalAmount() / $exchange->getExchange()), 3);
                        if ($approval_amount <= $config->getMaxAutomaticAmount()) {
                            $this->ignore_cache = true;
                            $connection = $this->repository->getConnection();
                            try {
                                $connection->beginTransaction();
                                $this->approval($model, true, '0', 'admin', $exchange->getExchange());
                                $connection->commit();
                            } catch (\Exception $e) {
                                $connection->rollBack();
                                $this->log->error('Error executing automatic approval', ['error' => $e]);
                            }
                            $this->ignore_cache = false;
                        }
                    }
                }
            }
        }

        return $model;
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

    public function getTypeName($type)
    {
        return SouthbayReturnConfig::getTypeName($type);
    }

    public function cancelableByCustomer($field)
    {
        if ($field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT) {
            return true;
        } else if ($field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD) {
            return true;
        } else if ($field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_FAIL_INIT) {
            return true;
        }

        return false;
    }

    public function printable($item)
    {
        if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_FAIL_INIT) {
            return true;
        } else if ($item->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD) {
            return true;
        }

        return false;
    }

    public function availableForApproval($field)
    {
        return in_array($field->getStatus(), $this->getPendingApprovalStatus());
    }

    public function availableForEditReception($field)
    {
        if ($field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_RECEIVED) {
            return true;
        }

        return false;
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $field
     * @return bool
     */
    public function availableForReception($field)
    {
        if ($field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_FAIL_INIT
            || $field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $field
     * @return bool
     */
    public function availableForEditControlQa($field)
    {
        if ($field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA
            || $field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA_GOOD) {
            return true;
        }

        return false;
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $field
     * @return bool
     */
    public function availableForControlQa($field)
    {
        if ($field->getStatus() === \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_RECEIVED) {
            return true;
        }

        return false;
    }

    public function findPendingApprovalById($id)
    {
        $collection = $this->_allPendingApproval(1);
        return $collection->getItemById($id);
    }

    public function allPendingApproval($current_page = 1)
    {
        $collection = $this->_allPendingApproval($current_page);
        return $collection->load(false, true);
    }

    public function searchPendingApproval($search, $current_page = 1)
    {
        $collection = $this->_allPendingApproval($current_page);
        return $this->_searchPending($search, $collection, $current_page);
    }

    public function allPendingControlQa($current_page = 1)
    {
        $collection = $this->_allPendingControlQa($current_page);
        return $collection->load();
    }

    public function searchPendingControlQa($search, $current_page = 1)
    {
        $collection = $this->_allPendingControlQa($current_page);
        return $this->_searchPending($search, $collection, $current_page);
    }

    public function allPendingReception($current_page = 1)
    {
        $collection = $this->_allPendingReception($current_page);
        return $collection->load();
    }

    public function searchPendingReception($search, $current_page = 1)
    {
        $collection = $this->_allPendingReception($current_page);
        return $this->_searchPending($search, $collection, $current_page);
    }

    public function _searchPending($search, $collection, $current_page = 1)
    {
        $filterReturnId = $this->filterBuilder
            ->setField(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_ID)
            ->setValue("%$search%")
            ->setConditionType('like')
            ->create();

        $filterCustomer = $this->filterBuilder
            ->setField(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_CUSTOMER_NAME)
            ->setValue("%$search%")
            ->setConditionType('like')
            ->create();

        $filterGroup = $this->filterGroupBuilder->setFilters([
            $filterCustomer, $filterReturnId
        ])->create();

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setFilterGroups([$filterGroup]);

        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->load();

        return $collection;
    }

    private function _allPendingReception($current_page)
    {
        $collection = $this->_allPending($current_page);

        $this->filterByCountries($collection, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_RECEPTION);
        $this->filterByTypeReturn($collection, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_RECEPTION);

        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS, ['in' => [
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_FAIL_INIT,
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD
        ]]);

        return $collection;
    }

    private function _allPendingControlQa($current_page)
    {
        $collection = $this->_allPending($current_page);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS, ['in' => [
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_RECEIVED
        ]]);

        $this->filterByCountries($collection, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CONTROL_QA);
        $this->filterByTypeReturn($collection, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CONTROL_QA);

        return $collection;
    }

    private function _allPendingApproval($current_page)
    {
        $collection = $this->_allPending($current_page);
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS,
            ['in' => $this->getPendingApprovalStatus()]
        );

        $this->filterByCountries($collection, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);
        $this->filterByTypeReturn($collection, \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);

        return $collection;
    }

    private function getPendingApprovalStatus()
    {
        return [
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA,
            \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT
        ];
    }

    private function _allPending($current_page)
    {
        /**
         * @var AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->setPageSize(30);
        $collection->setCurPage($current_page);
        $collection->setOrder(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_ID, 'DESC');

        return $collection;
    }

    private function filterByTypeReturn($collection, $type)
    {
        $result = [];
        $values = $this->helper->getTypeReturnByTypeRol($type);

        if (empty($values)) {
            $result[] = ['-'];
        } else {
            $result = $values;
        }

        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_TYPE, ['in' => $result]);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $type_rol
     * @return void
     */
    private function filterByCountries($collection, $type_rol)
    {
        $result = [];
        $values = $this->helper->getCountriesByTypeRol($type_rol);

        if (empty($values)) {
            $result[] = ['-'];
        } else {
            $result = $values;
        }

        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_COUNTRY_CODE, ['in' => $result]);
    }

    public function getArchivedDataProviderCollection()
    {
        $collection = $this->getDataproviderCollection();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS,
            [
                'in' => [\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_ARCHIVED]
            ]
        );

        return $collection;
    }

    public function getPendingConfirmationDataProviderCollection()
    {
        $collection = $this->getDataproviderCollection();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_STATUS,
            [
                'in' => [
                    \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL,
                    \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA_GOOD
                ]
            ]
        );

        return $collection;
    }

    public function getDataproviderCollection()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->join(['control_qa' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa::TABLE],
            "control_qa.southbay_return_id = main_table.southbay_return_id"
        );

        $collection
            ->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                "main_table.*",
                "control_qa.southbay_return_control_qa_total_real as southbay_return_control_qa_total_real",
                "control_qa.southbay_return_control_qa_total_missing as southbay_return_control_qa_total_missing",
                "control_qa.southbay_return_control_qa_total_extra as southbay_return_control_qa_total_extra"
            ]);

        $countries = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK);

        if (empty($countries)) {
            $countries = ['-'];
        }

        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::ENTITY_COUNTRY_CODE, ['in' => $countries]);

        return $collection;
    }

    /**
     * @param \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $model
     * @return void
     */
    public function revert($model)
    {
        if ($model->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_APPROVAL_GOOD) {
            $this->revertApproval($model->getId());
            $model->setStatus(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_GOOD_INIT);
        }

        if ($model->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA_GOOD
            || $model->getStatus() == \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::STATUS_CODE_CONTROL_QA_GOOD) {

        }

        $this->repository->save($model);
    }

    private function revertApproval($return_product_id)
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->financialApprovalCollectionFactory->create();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_RETURN_ID,
            ['eq' => $return_product_id]);
        $collection->load();

        if ($collection->count() == 0) {
            return;
        }

        $field = $collection->getFirstItem();
        $this->repository_financial_approval->delete($field);
    }

    public function addJoinsToCollection($collection)
    {


        $collection->getSelect()
            ->joinLeft(
                ['reception' => $collection->getTable('southbay_return_reception')],
                'main_table.southbay_return_id = reception.southbay_return_id',
                ['reception_date' => 'reception.created_at']
            )
            ->joinLeft(
                ['control_qa' => $collection->getTable('southbay_return_control_qa')],
                'main_table.southbay_return_id = control_qa.southbay_return_id',
                ['control_qa_date' => 'control_qa.created_at']
            )
            ->joinLeft(
                ['financial_approval' => $collection->getTable('southbay_return_financial_approval')],
                'main_table.southbay_return_id = financial_approval.southbay_return_id',
                ['approval_date' => 'financial_approval.created_at']
            )
            ->joinLeft(
                [
                    'latest_sap_interface' => new \Zend_Db_Expr(
                        "(SELECT southbay_sap_interface_ref, MAX(created_at) AS created_at
                  FROM {$collection->getTable('southbay_sap_interface')}
                  WHERE " . \Southbay\ReturnProduct\Api\Data\SouthbaySapInterface::ENTITY_FROM . " = 'rtv'
                  GROUP BY southbay_sap_interface_ref)"
                    )
                ],
                'main_table.southbay_return_id = latest_sap_interface.southbay_sap_interface_ref',
                ['confirmation_date' => 'latest_sap_interface.created_at']
            )
            ->group('main_table.southbay_return_id');

        $collection->addFilterToMap('created_at', 'main_table.created_at');
        $collection->addFilterToMap('reception_date', 'reception.created_at');
        $collection->addFilterToMap('control_qa_date', 'control_qa.created_at');
        $collection->addFilterToMap('approval_date', 'financial_approval.created_at');
        $collection->addFilterToMap('confirmation_date', 'latest_sap_interface.created_at');


    }


}
