<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\Invoice;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoice as Entity;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoice as EntityResourceModel;

class InvoiceSearchResult extends SearchResult
{
    private $helper;
    private $context;

    public function __construct(\Southbay\ReturnProduct\Helper\Data $helper,
                                \Magento\Backend\App\Action\Context $context,
                                EntityFactory                       $entityFactory,
                                Logger                              $logger,
                                FetchStrategy                       $fetchStrategy,
                                EventManager                        $eventManager)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, Entity::TABLE, EntityResourceModel::class);
        $this->helper = $helper;
        $this->context  =$context;
        $this->init();
    }

    private function init()
    {
       $countries = $this->helper->getSapCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK);

        if (empty($countries)) {
            $this->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayInvoice::ENTITY_COUNTRY_CODE, ['eq' => '1']);
        } else {
            $this->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayInvoice::ENTITY_COUNTRY_CODE, ['in' => $countries]);
        }

        $this->join(['sold_to' => \Southbay\CustomCustomer\Api\Data\SoldToInterface::TABLE],
            "sold_to.southbay_sold_to_customer_code = main_table.southbay_customer_code " .
            "OR sold_to.southbay_sold_to_customer_code_old = main_table.southbay_customer_code"
        );
        $this->join(['ship_to' => \Southbay\CustomCustomer\Api\Data\ShipToInterface::TABLE],
            "(ship_to.southbay_ship_to_code = main_table.southbay_customer_ship_to_code " .
            "OR ship_to.southbay_ship_to_old_code = main_table.southbay_customer_ship_to_code) " .
            "AND (ship_to.southbay_ship_to_customer_code = sold_to.southbay_sold_to_customer_code)"
        );
        $this->join(['map_country' => \Southbay\CustomCustomer\Api\Data\MapCountryInterface::TABLE],
            "map_country.southbay_map_sap_country_code = main_table.southbay_invoice_country_code "
        );
    }

    protected function _initSelectFields()
    {
        parent::_initSelectFields();

        $this->_select->setPart(\Magento\Framework\DB\Select::COLUMNS, ['sold_to', 'southbay_sold_to_country_code', 'southbay_sold_to_country_code']);
        $this->_select->setPart(\Magento\Framework\DB\Select::COLUMNS, ['sold_to', 'southbay_sold_to_customer_code', 'southbay_sold_to_customer_code']);
        $this->_select->setPart(\Magento\Framework\DB\Select::COLUMNS, ['sold_to', 'southbay_sold_to_customer_name', 'southbay_sold_to_customer_name']);
        $this->_select->setPart(\Magento\Framework\DB\Select::COLUMNS, ['ship_to', 'southbay_ship_to_code', 'southbay_ship_to_code']);
        $this->_select->setPart(\Magento\Framework\DB\Select::COLUMNS, ['map_country', 'southbay_map_country_code', 'southbay_map_country_code']);

        return $this;
    }

    public function getItems()
    {
        $result = [];
        $items = parent::getItems();
        $url_builder = $this->context->getBackendUrl();

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayInvoice $item
         */
        foreach ($items as $item) {
            $url = $url_builder->getUrl('southbay_return_product/invoice/view', ['id' => $item->getData(Entity::ENTITY_ID)]);
            $item->setData('link_label', __('Ver'));
            $item->setData('link', $url);

            $result[] = $item;
        }

        return $result;
    }
}
