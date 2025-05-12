<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\ConfigApproval;

use Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa as Entity;
use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayRolConfigRtvCollectionFactory as CollectionFactory;

class ConfigApprovalDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    private $collection_factory;
    private $context;
    private $log;
    private $loadedData;

    public function __construct(\Magento\Backend\App\Action\Context $context,
                                CollectionFactory                   $collection_factory,
                                                                    $name,
                                                                    $primaryFieldName,
                                                                    $requestFieldName,
                                \Psr\Log\LoggerInterface            $log,
                                array                               $meta = [],
                                array                               $data = [])
    {
        $this->collection_factory = $collection_factory;
        $this->context = $context;
        $this->log = $log;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data);
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            $this->collection = $this->initCollection();
        }
        return $this->collection;
    }

    protected function initCollection()
    {
        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->collection_factory->create();
        $collection->addFieldToFilter(
            \Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv::ENTITY_TYPE_ROL,
            ['eq' => \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL]
        );

        return $collection;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $id = $this->context->getRequest()->getParam('id');

        if ($id) {
            $item = $this->collection->getItemById($id);
            if ($item) {
                $data = $item->getData();
                $data['southbay_rol_config_return_approval_use_amount_limit'] = (bool)$data['southbay_rol_config_return_approval_use_amount_limit'];
                $data['require_all_members'] = (bool)$data['require_all_members'];
                $this->loadedData[$item->getId()]['fields'] = $data;
            }

            return $this->loadedData;
        }

        $this->loadedData['items'] = [];
        $items = $this->getCollection()->getItems();
        $url_builder = $this->context->getBackendUrl();

        foreach ($items as $item) {
            $id = $item->getId();
            $url = $url_builder->getUrl('southbay_return_product/configApproval/view', ['id' => $id]);

            $item->setData('require_all_members', $item->getData('require_all_members') ? __('Yes') : __('No'));
            $item->setData('link_label', __('Editar'));
            $item->setData('link', $url);
            $this->loadedData['items'][] = $item->getData();
        }

        $this->loadedData['totalRecords'] = $this->getCollection()->getSize();

        return $this->loadedData;
    }
}
