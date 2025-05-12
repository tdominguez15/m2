<?php

namespace Southbay\Product\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\ViewModel\SoldToViewModel;
use Magento\Store\Model\StoreManagerInterface;

class SegmentFilter extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SoldToViewModel
     */
    protected $soldToViewModel;

    /**
     * @var ConfigStoreRepository
     */
    protected $configStoreRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    private $log;

    /**
     * SegmentFilter constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param SoldToViewModel $soldToViewModel
     * @param ConfigStoreRepository $configStoreRepository
     * @param StoreManagerInterface $_storeManager
     */
    public function __construct(
        Context                  $context,
        CollectionFactory        $productCollectionFactory,
        SoldToViewModel          $soldToViewModel,
        ConfigStoreRepository    $configStoreRepository,
        StoreManagerInterface    $_storeManager,
        \Psr\Log\LoggerInterface $log
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->soldToViewModel = $soldToViewModel;
        $this->configStoreRepository = $configStoreRepository;
        $this->_storeManager = $_storeManager;
        $this->log = $log;
        parent::__construct($context);
    }

    /**
     * Filter product collection IDs based on segmentation.
     *
     * @return array
     * @throws \Exception
     */
    public function filterCollectionIds()
    {
        $soldTo = $this->soldToViewModel->getSoldToFromSession();
        if (is_null($soldTo)) {
            throw new \Exception('Invalid request. Sold to not found');
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $config = $this->configStoreRepository->findByStoreId($storeId);
        if (is_null($config)) {
            throw new \Exception('Store not configured');
        }

        $country = $config->getSouthbayCountryCode();
        $segmentations = $soldTo->getSegmentation();

        if (empty($segmentations)) {
            $segmentations = ['n/a'];
        } elseif ($segmentations == '*all-for-southbay*' || $config->getSouthbayFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
            // No aplicar filtro si el cliente tiene todas las segmentaciones
            return [];
        } else {
            $segmentations = explode(',', $segmentations);
        }

        $this->log->debug('segmentations', [
            'store_id' => $storeId,
            'country' => $country,
            'sold_to' => $soldTo->getCustomerCode(),
            'name' => $soldTo->getCustomerName(),
            'segmentations' => $segmentations
        ]);

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->setStoreId($storeId);

        foreach ($segmentations as $segmentation) {
            $productCollection->addAttributeToFilter('southbay_channel_level_list', ['like' => "%;$country:$segmentation;%"], 'left');
        }

        if ($config->getSouthbayFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
            $productCollection->addAttributeToFilter('southbay_channel_level_list', ['eq' => ""], 'left');
        }

        $productCollection->load();

        $filteredIds = [];
        foreach ($productCollection as $product) {
            $filteredIds[] = $product->getId();
        }

        if (empty($filteredIds)) {
            $filteredIds = [-1];
        }

        return $filteredIds;
    }
}
