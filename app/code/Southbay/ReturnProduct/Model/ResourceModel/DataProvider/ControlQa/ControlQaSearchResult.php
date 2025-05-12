<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\ControlQa;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Southbay\ReturnProduct\Model\ResourceModel\DataProvider\SearchResultBase;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQa as EntityResourceModel;
use \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa as Entity;

class ControlQaSearchResult extends SearchResultBase
{
    private $helper;

    public function __construct(EntityFactory                       $entityFactory,
                                \Southbay\ReturnProduct\Helper\Data $helper,
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
        $countries = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CONTROL_QA);

        if (empty($countries)) {
            return [];
        }

        $result = [];
        $items = parent::getItems();
        $url_builder = $this->context->getBackendUrl();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa $item
         */
        foreach ($items as $item) {
            if (in_array($item->getData(Entity::ENTITY_COUNTRY_CODE), $countries)) {
                $url = $url_builder->getUrl('southbay_return_product/controlqa/view', ['id' => $item->getData(Entity::ENTITY_ID)]);
                $item->setData('link_label', __('Ver'));
                $item->setData('link', $url);

                $result[] = $item;
            }
        }

        return $result;
    }
}
