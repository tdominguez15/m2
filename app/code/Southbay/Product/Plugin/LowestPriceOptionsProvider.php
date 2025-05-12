<?php

namespace Southbay\Product\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class LowestPriceOptionsProvider implements LowestPriceOptionsProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var LinkedProductSelectBuilderInterface
     */
    private $linkedProductSelectBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Key is product id and store id. Value is array of prepared linked products
     *
     * @var array
     */
    private $linkedProductMap;

    private $log;

    private $productDataHelp;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection                  $resourceConnection,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder,
        CollectionFactory                   $collectionFactory,
        StoreManagerInterface               $storeManager,
        \Southbay\Product\Helper\Data       $productDataHelp,
        \Psr\Log\LoggerInterface            $log
    )
    {
        $this->resource = $resourceConnection;
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->log = $log;
        $this->productDataHelp = $productDataHelp;
    }

    /**
     * @inheritdoc
     */
    public function getProducts(ProductInterface $product)
    {
        $productId = $product->getId();
        $storeId = $product->getStoreId() ?: $this->storeManager->getStore()->getId();
        $key = $storeId . '-' . $productId;
        if (!isset($this->linkedProductMap[$key])) {
            $childProducts = $this->productDataHelp->getProductChildren($product);
            // $this->log->debug('LowestPriceOptionsProvider. Filter by product', ['product_id' => $productId, 'sku' => $product->getSku(), 'c' => count($childProducts)]);

            $this->linkedProductMap[$key] = $childProducts;
        }
        return $this->linkedProductMap[$key];
    }
}
