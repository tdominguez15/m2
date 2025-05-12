<?php

namespace Southbay\ApproveOrders\Controller\Refill;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Api\ConfigStoreRepositoryInterface;
use Southbay\Product\Helper\Data as SouthbayHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Model\ResourceModel\ConfigStore\CollectionFactory as ConfigStoreCollection;

class Index implements HttpGetActionInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SouthbayHelper
     */
    protected $southbayHelper;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ConfigStoreCollection
     */
    protected $configStoreCollection;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param SouthbayHelper $southbayHelper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigStoreCollection $configStoreCollection
     */
    public function __construct(
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        SouthbayHelper $southbayHelper,
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        ConfigStoreCollection $configStoreCollection,
    ) {
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->southbayHelper = $southbayHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->configStoreCollection = $configStoreCollection;

    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        try {

            $storeCollection = $this->configStoreCollection->create();
            $storeCollection->addFieldToFilter('southbay_function_code', ['eq' =>  ConfigStoreRepositoryInterface::SOUTHBAY_FUTURES]);
            $storeCodes = $storeCollection->getColumnValues('southbay_store_code');
            $websiteIds = [];
            foreach ($storeCodes as $storeCode) {
                $store = $this->storeManager->getStore($storeCode);
                $websiteId = $store->getWebsiteId();
                if (!in_array($websiteId, $websiteIds)) {
                    $websiteIds[] = $websiteId;
                }
            }

            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('sku')
                ->addAttributeToFilter('type_id', Type::TYPE_SIMPLE)
                ->addWebsiteFilter($websiteIds);


            foreach ($productCollection as $product) {
                $this->southbayHelper->updateProductStock($product->getSku(), 1, 99999);
            }

            $result = ['success' => true, 'message' => 'Cron job executed successfully'];
        } catch (\Exception $e) {
            $this->logger->error('Error executing cron job: ' . $e->getMessage());
            $result = ['success' => false, 'message' => 'Error executing cron job'];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
