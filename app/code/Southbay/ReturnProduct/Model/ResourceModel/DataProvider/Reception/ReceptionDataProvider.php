<?php

namespace Southbay\ReturnProduct\Model\ResourceModel\DataProvider\Reception;

use Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnReceptionCollectionFactory as CollectionFactory;
use Southbay\ReturnProduct\Model\ResourceModel\DataProvider\DataProviderBase;

class ReceptionDataProvider extends DataProviderBase
{
    private $helper;

    public function __construct(\Magento\Backend\App\Action\Context                                                  $context,
                                \Southbay\ReturnProduct\Helper\Data                                                  $helper,
                                \Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnTypeOptionsProvider          $returnTypeOptionsProvider,
                                \Southbay\ReturnProduct\Block\Adminhtml\Form\Grid\ReturnProductClientOptionsProvider $clientOptionsProvider,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository          $returnProductRepository,
                                CollectionFactory                                                                    $collection_factory,
                                                                                                                     $name,
                                                                                                                     $primaryFieldName,
                                                                                                                     $requestFieldName,
                                \Psr\Log\LoggerInterface                                                             $log,
                                array                                                                                $meta = [],
                                array                                                                                $data = [])
    {
        $this->helper = $helper;
        $this->collection_factory = $collection_factory;
        parent::__construct(
            $context,
            $returnTypeOptionsProvider,
            $clientOptionsProvider,
            $returnProductRepository,
            $name,
            $primaryFieldName,
            $requestFieldName,
            $log,
            $meta,
            $data);
    }

    protected function initCollection()
    {
        $countries = $this->helper->getCountriesByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_RECEPTION);

        if (empty($countries)) {
            $countries = ['-'];
        }

        /**
         * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
         */
        $collection = parent::initCollection();
        $collection->addFieldToFilter(\Southbay\ReturnProduct\Api\Data\SouthbayReturnReception::ENTITY_COUNTRY_CODE, ['in' => $countries]);

        return $collection;
    }

    protected function getItem($item)
    {
        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $item
         */
        $field = $this->returnProductRepository->findById($item['fields']['detail']['southbay_return_id']);
        $item['fields']['detail']['southbay_return_total_packages'] = $field->getLabelTotalPackages();
        $item['fields']['edit_mode'] = ($this->returnProductRepository->availableForEditReception($field) ? 'edit' : 'view');

        return $item;
    }
}
