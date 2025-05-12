<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Config\Grid\Column;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Directory\Model\CountryFactory;

class CountryRenderer extends AbstractRenderer
{
    private $log;
    private $countryFactory;

    public function __construct(Context        $context,
                                CountryFactory $countryFactory,
                                array          $data = [])
    {
        $this->log = $context->getLogger();
        $this->countryFactory = $countryFactory;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $column_id = $this->getColumn()->getId();
        $country_code = $row->getData($column_id);

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Psr\Log\LoggerInterface $log
         */
        $log = $objectManager->get('Psr\Log\LoggerInterface');

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = $this->countryFactory->create()->getCollection();
        $collection->addFieldToFilter('country_id', ['eq' => $country_code]);
        $collection->load();

        $item = $collection->getFirstItem();

        return __($item->getName());
    }
}
