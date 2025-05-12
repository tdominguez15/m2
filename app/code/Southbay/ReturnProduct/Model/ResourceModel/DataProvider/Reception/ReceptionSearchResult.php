<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\Reception;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Southbay\ReturnProduct\Model\ResourceModel\DataProvider\SearchResultBase;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnReception as EntityResourceModel;
use \Southbay\ReturnProduct\Api\Data\SouthbayReturnReception as Entity;

class ReceptionSearchResult extends SearchResultBase
{
    private $helper;

    public function __construct(\Southbay\ReturnProduct\Helper\Data $helper,
                                EntityFactory                       $entityFactory,
                                \Magento\Backend\App\Action\Context $context,
                                Logger                              $logger,
                                FetchStrategy                       $fetchStrategy,
                                EventManager                        $eventManager,
                                                                    $mainTable = Entity::TABLE,
                                                                    $resourceModel = EntityResourceModel::class)
    {
        $this->helper = $helper;
        parent::__construct($entityFactory, $context, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function getItems()
    {
        $countries = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_RECEPTION);

        if (empty($countries)) {
            return [];
        }

        $result = [];
        parent::load(false, true);
        $items = parent::getItems();
        $url_builder = $this->context->getBackendUrl();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnReception $item
         */
        foreach ($items as $item) {
            if (in_array($item->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnReception::ENTITY_COUNTRY_CODE), $countries)) {
                $url = $url_builder->getUrl('southbay_return_product/reception/view', ['id' => $item->getData(Entity::ENTITY_ID)]);
                $item->setData('link_label', __('Ver'));
                $item->setData('link', $url);

                $result[] = $item;
            }
        }

        return $result;
    }

}
