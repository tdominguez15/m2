<?php

namespace Southbay\Product\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\Product\Model\Import\ProductImporter;
use Southbay\Product\Model\ProductExclusionRepository;
use Southbay\Product\Model\ResourceModel\SeasonRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\CollectionFactory as StockSourceLinkCollectionFactory;
use Southbay\CustomCustomer\Model\ResourceModel\MapCountry\CollectionFactory as MapCountryCollectionFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class Data extends AbstractHelper
{
    private $productRepository;
    private $log;
    private $storeManager;
    private $attributeRepository;
    private $seasonRepository;
    private $configurableProductType;
    private $stockRegistry;
    private $configStoreRepository;
    private $getSalableQuantityDataBySku;
    private $getProductSalableQty;
    private $getStockBySalesChannel;
    private $stockRepository;
    private $salesChannelFactory;
    private $sourceItemsSave;
    private $sourceItemFactory;
    private $getSourceItemsBySku;
    private $stockSourceLinkCollectionFactory;
    private $mapCountryCollectionFactory;
    private $extensionAttributesFactory;
    private $stockItemFactory; // Agregar esta variable privada

    private $productExclusionRepository;

    private $resource;
    protected $configurableProduct;

    public function __construct(
        Configurable $configurableProduct,
        Context                                   $context,
        ProductRepositoryInterface                $productRepository,
        StoreManagerInterface                     $storeManager,
        AttributeRepositoryInterface              $attributeRepository,
        SeasonRepository                          $seasonRepository,
        ConfigurableProductType                   $configurableProductType,
        LoggerInterface                           $log,
        StockRegistryInterface                    $stockRegistry,
        ConfigStoreRepository                     $configStoreRepository,
        GetSalableQuantityDataBySku               $getSalableQuantityDataBySku,
        GetProductSalableQtyInterface             $getProductSalableQty,
        GetStockBySalesChannelInterface           $getStockBySalesChannel,
        StockRepositoryInterface                  $stockRepository,
        SalesChannelInterfaceFactory              $salesChannelFactory,
        SourceItemsSaveInterface                  $sourceItemsSave,
        SourceItemInterfaceFactory                $sourceItemFactory,
        GetSourceItemsBySkuInterface              $getSourceItemsBySku,
        StockSourceLinkCollectionFactory          $stockSourceLinkCollectionFactory,
        MapCountryCollectionFactory               $mapCountryCollectionFactory,
        ExtensionAttributesFactory                $extensionAttributesFactory,
        StockItemInterfaceFactory                 $stockItemFactory, // Agregar esta línea
        ProductExclusionRepository                $productExclusionRepository,
        \Magento\Framework\App\ResourceConnection $resource,

    )
    {
        parent::__construct($context);
        $this->configurableProduct = $configurableProduct;
        $this->productRepository = $productRepository;
        $this->log = $log;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->seasonRepository = $seasonRepository;
        $this->configurableProductType = $configurableProductType;
        $this->stockRegistry = $stockRegistry;
        $this->configStoreRepository = $configStoreRepository;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
        $this->stockRepository = $stockRepository;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->stockSourceLinkCollectionFactory = $stockSourceLinkCollectionFactory;
        $this->mapCountryCollectionFactory = $mapCountryCollectionFactory;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->productExclusionRepository = $productExclusionRepository;
        $this->resource = $resource;
    }

    public function getCurrentSeason($store_id)
    {
        return $this->seasonRepository->findCurrent($store_id);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getProductValues(string $sku, array $attrsCodeList): array
    {
        $result = [];

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $product = $this->productRepository->get($sku, false, $storeId);

            foreach ($attrsCodeList as $code) {
                $value = $product->getData($code);
                $result[$code] = $value;

                $attribute = $this->attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $code);

                if ($attribute->getFrontendInput() === 'select' && $value !== null) {
                    foreach ($attribute->getOptions() as $option) {
                        if ($option->getValue() == $value) {
                            $result[$code] = $option->getLabel();
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log->error("Error getting product values for SKU: $sku. Message: " . $e->getMessage());
        }

        return $result;
    }


    public function mapFirstProductVariantFromCatalogProductCollection($collection)
    {
        $result = [];

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($collection as $product) {
            $result[$product->getId()] = $this->getFirstProductVariant($product);
        }

        return $result;
    }

    public function getFirstProductVariant(\Magento\Catalog\Model\Product $product)
    {
        $childProducts = $this->getProductVariants($product);
        $store = $product->getStore();
        $website_id = $store->getWebsiteId();

        if (!empty($childProducts)) {
            foreach ($childProducts as $child) {
                if ($child->getId() != $product->getId()) {
                    if ($this->productInWebsite($child->getId(), $website_id)) {
                        return $child;
                    }
                }
            }
        }

        return $product;
    }

    public function getProductVariants(\Magento\Catalog\Model\Product $product)
    {
        $result = [];

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $web_site_id = $product->getStore()->getWebsiteId();
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            $excluded = $this->productExclusionRepository->getExcludedSkus($product->getStore()->getStoreId());

            foreach ($childProducts as $child) {
                if ($this->productInWebsite($child->getId(), $web_site_id)
                    && !in_array($child->getSku(), $excluded)
                ) {
                    $result[] = $child;
                }
            }
        }

        return $result;
    }

    private function getProductSizeOptions(\Magento\Catalog\Model\Product $product)
    {
        $p = new ProductImporter();
        $attrs = $p->findAttributeByCode('southbay_size');
        return $attrs->getOptions();
    }

    private function getDepartments(\Magento\Catalog\Model\Product $product)
    {
        $p = new ProductImporter();
        $attrs = $p->findAttributeByCode('southbay_department');
        return $attrs->getOptions();
    }

    public function getChildrenLabels(\Magento\Catalog\Model\Product $product)
    {
        $result = [];
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $options = $this->getProductSizeOptions($product);
            $map = [];
            //        $stock = $this->getChildrenStock($product);
            $sizes = $this->sizes();

            foreach ($options as $option) {
                $weight = 1000;
                foreach ($sizes as $key => $value) {
                    if ($option->getLabel() == $value) {
                        $weight = $key;
                        break;
                    }
                }

                $map[$option->getValue()] = [
                    'value' => $option->getValue(),
                    'label' => $option->getLabel(),
                    'weight' => $weight
                ];
            }

            $web_site_id = $product->getStore()->getWebsiteId();
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            $excluded = $this->productExclusionRepository->getExcludedSkus($product->getStore()->getStoreId());

            foreach ($childProducts as $child) {
                if ($this->productInWebsite($child->getId(), $web_site_id)
                    && !in_array($child->getSku(), $excluded)
                ) {
                    $size = $child->getSouthbaySize();
                    $result[] = $map[$size];
                }
            }
        }

        return $this->sortSizesItems($result);
    }

    public function productInWebsite($product_id, $website_id)
    {
        $sql = "SELECT count(*) FROM catalog_product_website WHERE product_id = $product_id AND website_id = $website_id";
        $connection = $this->resource->getConnection();
        return $connection->fetchOne($sql);
    }

    public function getChildrenLabelsAndStock(\Magento\Catalog\Model\Product $product)
    {
        $result = [];
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $options = $this->getProductSizeOptions($product);
            $map = [];

            $sizes = $this->sizes();
            $stock = $this->getChildrenStock($product);

            foreach ($options as $option) {
                $weight = 1000;
                foreach ($sizes as $key => $value) {
                    if ($option->getLabel() == $value) {
                        $weight = $key;
                        break;
                    }
                }

                $map[$option->getValue()] = [
                    'value' => $option->getValue(),
                    'label' => $option->getLabel(),
                    'weight' => $weight,
                    'salableQty' => isset($stock[$option->getValue()]) ? $stock[$option->getValue()]->getQty() : 0];

            }

            $web_site_id = $product->getStore()->getWebsiteId();
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            $excluded = $this->productExclusionRepository->getExcludedSkus($product->getStore()->getStoreId());

            foreach ($childProducts as $child) {
                $size = $child->getSouthbaySize();
                if ($this->productInWebsite($child->getId(), $web_site_id)
                    && !in_array($child->getSku(), $excluded)
                    && isset($stock[$size]) && $stock[$size]->getIsInStock() && $stock[$size]->getTypeId() == 'simple') {
                    $result[] = $map[$size];
                }
            }
        }

        return $this->sortSizesItems($result);
    }

    public function getChildrenStock($product)
    {
        $store = $this->storeManager->getStore();

        $product = $this->productRepository->getById($product->getId(), false, $store->getId());
        $result = [];
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        $stockId = $this->getStockIdByStore($store);
        $parentManageStock = $this->stockRegistry->getStockItem($product->getId())->getManageStock();

        foreach ($childProducts as $option) {
            $productId = $option->getId();
            $stockItem = $this->stockRegistry->getStockItem($productId);
            $sku = $option->getSku();
            $sourceItems = $this->getSourceItemsBySku->execute($sku);
            $sourceCode = $this->getSourcesByStockId($stockId);
            $optionManageStock = $stockItem->getManageStock();
            foreach ($sourceItems as $sourceItem) {
                if ($sourceItem->getSourceCode() == $sourceCode && $optionManageStock && $parentManageStock) {
                    $salableQty = $this->getProductSalableQty->execute($option->getSku(), $stockId);
                    if ($salableQty > 0) {
                        $stockItem->setQty($salableQty);
                        $stockItem->setIsInStock($sourceItem->getStatus());
                        $size = $option->getSouthbaySize();
                        $result[$size] = $stockItem;
                    }
                }
            }
        }

        return $result;
    }

    public function getStockByProduct($product, $storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $stockId = $this->getStockIdByStore($store);
        $sku = $product->getSku();
        if (empty($sku)) {
            throw new \InvalidArgumentException('El SKU del producto no puede estar vacío.');
        }
        $productStocks = $this->getSalableQuantityDataBySku->execute($sku);
        if (!is_array($productStocks) || empty($productStocks)) {
            throw new \RuntimeException('No se encontró cantidad vendible para el SKU proporcionado.');
        }
        foreach ($productStocks as $productStock) {
            if ($productStock['stock_id'] == $stockId)
                return $productStock;
        }
        return throw new \RuntimeException('No se encontró cantidad vendible para el SKU proporcionado.');
    }

    public function sortSizesItems($items)
    {
        usort($items, function ($item1, $item2) {
            $item1_key = $item1['weight'];
            $item2_key = $item2['weight'];

            if ($item1_key == $item2_key) {
                return 0;
            } else if ($item1_key < $item2_key) {
                return -1;
            } else {
                return 1;
            }
        });

        return $items;
    }

    public function getMonthForDeliveryFromCurrent($store = null)
    {
        $store = $store ?? $this->storeManager->getStore();
        $config = $this->configStoreRepository->findByStoreId($store->getId());
        if ($config->getId() && $config->getFunctionCode() === ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
            $months[] = [
                'code' => 'atonce',
                'label' => 'At Once'
            ];
            return $months;
        }

        return $this->seasonRepository->getMonthForDeliveryFromCurrent($store);
    }
    public function getMonthForDeliveryFromCurrent2($store = null)
    {
        $sessionData = $this->checkoutSession->getMonthsForDelivery();
        // Obtener la tienda actual si no se proporciona
        $store = $store ?? $this->storeManager->getStore();
        $storeId = $store->getId();

        // Intentar obtener los datos desde la sesión


        if ($sessionData !== null && isset($sessionData[$storeId])) {
            return $sessionData[$storeId]; // Devolver caché si está disponible
        }

        // Consultar la configuración de la tienda
        $config = $this->configStoreRepository->findByStoreId($storeId);

        if ($config->getId() && $config->getFunctionCode() === ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
            $months = [['code' => 'atonce', 'label' => 'At Once']];
        } else {
            $months = $this->seasonRepository->getMonthForDeliveryFromCurrent($store);
        }

        // Guardar en la sesión para futuras consultas
        $sessionData[$storeId] = $months;
        $this->checkoutSession->setMonthsForDelivery($sessionData);

        return $months;
    }


    public function getTotalFromSeasonConfigBySize($product)
    {
        $totales = [];
        $values = $this->getProductSeasonConfig($product);

        foreach ($values as $month => $sizes) {
            foreach ($sizes as $size => $value) {
                if (!isset($totales[$size])) {
                    $totales[$size] = ['general' => 0, 'months' => []];
                }
                if (!isset($totales[$size]['months'][$month])) {
                    $totales[$size]['months'][$month] = 0;
                }
                $totales[$size]['general'] += $value;
                $totales[$size]['months'][$month] += $value;
            }
        }

        return $totales;
    }

    public function getTotalFromSeasonConfig($product)
    {
        $totales = [];
        $values = $this->getProductSeasonConfig($product);

        foreach ($values as $month => $sizes) {
            if (!isset($totales[$month])) {
                $totales[$month] = 0;
            }

            $_sizes = array_values($sizes);

            foreach ($_sizes as $value) {
                $totales[$month] += intval($value);
            }
        }

        return $totales;
    }

    public function getProductSeasonConfig($product)
    {
        $values = [];
        $custom = $product->getCustomOption('season_qty');
        if (!is_null($custom)) {
            $values = (empty($custom->getValue()) ? [] : json_decode($custom->getValue(), true));
        }
        return $values;
    }
    public function findParentProductByChild($productId)
    {
        $parentIds = $this->configurableProduct->getParentIdsByChild($productId);

        if (!empty($parentIds)) {
            return $this->productRepository->getById($parentIds[0]);
        }

        return null;
    }
    public function findParentProduct($productId)
    {
        $parentsId = $this->getParentIdsByChild($productId);

        $parent = null;

        if (!empty($parentsId)) {
            $parentId = $parentsId[0];
            $parent = $this->productRepository->getById($parentId);
        }

        return $parent;
    }

    public function isParent($productId, $posibleParentId)
    {
        $parentsId = $this->getParentIdsByChild($productId);
        return in_array($posibleParentId, $parentsId);
    }

    public function getParentIdsByChild($productId)
    {
        return $this->configurableProductType->getParentIdsByChild($productId);
    }

    public function sizes()
    {
        return array(
            '2C', '3C', '4C', '5C', '6C', '7C', '8C', '9C', '10C', '10.5C', '11C', '11.5C', '12C', '12.5C', '13C', '13.5C', '1Y', '1.5Y', '2Y', '2.5Y', '3Y', '3.5Y', '4Y', '4.5Y', '5Y', '5.5Y', '6Y', '6.5Y', '7Y', '3.5', '4', '4.5', '5', '5.5', '6', '6.5', '7', '7.5', '8', '8.5', '9', '9.5', '10', '10.5', '11', '11.5', '12', '12.5', '13', '13.5', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', 'XXS', 'XS', 'S', 'S/M', 'M', 'M/L', 'L', 'L/XL', 'XL', '2XL', '3XL', '4XL', 'S+', 'M+', 'L+', 'XL+', '28', '30', '32', '34', '36', 'MISC', '1SIZE', '1X', '3X', 'S-T', 'M-T', 'L-T', '1', '3', '4', '5', '7', '8', '9', 'XS', 'S', 'M', 'L', 'XL', 'YTH', 'PRO', 'XSA-C', 'SA-C', 'MA-B', 'MC-E', 'LA-B', 'LC-E', 'LF-G', 'XLA-B', 'XLC-E', 'XLF-G', 'XL2XL', '1XA-B', '2XLCE', '2XLFG'
        );
    }

    public function getStockIdByStore(StoreInterface $store)
    {
        $storeCode = $store->getCode();

        return $this->getStockIdByStoreCode($storeCode);
    }

    public function getStockIdByStoreCode($storeCode)
    {
        try {
            $salesChannel = $this->salesChannelFactory->create([
                'data' => [
                    'type' => \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                    'code' => $storeCode
                ]
            ]);

            $stock = $this->getStockBySalesChannel->execute($salesChannel);
            return $stock->getStockId();
        } catch (\Exception $e) {
            $this->log->error('Error obtaining stock ID for store: ' . $e->getMessage());
            return null;
        }
    }

    public function getSourceCodeByStoreId($store_id)
    {
        $store = $this->storeManager->getStore($store_id);
        return $this->getStockIdByStore($store);
    }

    public function getSourceCodeByStoreCode($storeCode)
    {
        $stockId = $this->getStockIdByStoreCode($storeCode);

        if (is_null($stockId)) {
            return null;
        }

        return $this->getSourcesByStockId($stockId, true);
    }

    public function updateProductStock($sku, $stockId, $quantity)
    {
        try {

            $sourceItems = $this->getSourceItemsBySku->execute($sku);
            $sourceCode = $this->getSourcesByStockId($stockId);

            $sourceItemFound = false;
            foreach ($sourceItems as $sourceItem) {
                if ($sourceItem->getSourceCode() == $sourceCode) {
                    $sourceItem->setQuantity($quantity);
                    $sourceItem->setStatus($quantity > 0 ? \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK : \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK);
                    $sourceItemFound = true;
                    break;
                }
            }

            // crear source item si no existe
            if (!$sourceItemFound) {
                $sourceItem = $this->sourceItemFactory->create();
                $sourceItem->setSku($sku);
                $sourceItem->setSourceCode($sourceCode);
                $sourceItem->setQuantity($quantity);
                $sourceItem->setStatus($quantity > 0 ? \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK : \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK);
                $sourceItems[] = $sourceItem;
            }

            $this->sourceItemsSave->execute($sourceItems);

            return true;
        } catch (\Exception $e) {
            $this->log->error('Error updating stock for SKU ' . $sku . ' in stockID ' . $sourceCode . ': ' . $e->getMessage());
            return false;
        }
    }

    public function getSourcesByStockId($stockId, $returnNullWhenError = false)
    {
        try {
            $collection = $this->stockSourceLinkCollectionFactory->create();
            $collection->addFieldToFilter('stock_id', $stockId);
            $source = $collection->getFirstItem();
            return $source->getSourceCode();
        } catch (\Exception $e) {
            $this->log->error('Error getting sources for stockID ' . $stockId . ': ' . $e->getMessage());
            if ($returnNullWhenError) {
                return null;
            } else {
                return 'Error getting sources for stockID ' . $stockId;
            }
        }
    }

    public function setDayToFirst(\DateTime $date): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d', $date->format('Y-m-01'));
    }

    public function getProductChildren(\Magento\Catalog\Model\Product $product)
    {
        $result = [];
        $store = $product->getStore();
        $web_site_id = $store->getWebsiteId();
        $is_atOnce = $this->configStoreRepository->isAtOnce($store->getId());
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        $excluded = $this->productExclusionRepository->getExcludedSkus($store->getId());

        if ($is_atOnce) {
            $stockId = $this->getStockIdByStore($store);
            $sourceCode = $this->getSourcesByStockId($stockId);
            $parentManageStock = $this->stockRegistry->getStockItem($product->getId())->getManageStock();

            if (!$parentManageStock) {
                return $result;
            }
        }

        foreach ($childProducts as $child) {
            $productId = $child->getId();
            $sku = $child->getSku();

            if ($this->productInWebsite($productId, $web_site_id)
                && !in_array($sku, $excluded)) {
                if ($is_atOnce) {
                    $stockItem = $this->stockRegistry->getStockItem($productId);
                    $sourceItems = $this->getSourceItemsBySku->execute($sku);
                    $optionManageStock = $stockItem->getManageStock();

                    foreach ($sourceItems as $sourceItem) {
                        if ($sourceItem->getSourceCode() == $sourceCode && $optionManageStock) {
                            $salableQty = $this->getProductSalableQty->execute($sku, $stockId);
                            if ($salableQty > 0) {
                                $stockItem->setQty($salableQty);
                                $stockItem->setIsInStock($sourceItem->getStatus());
                                $result[] = $child;
                                break;
                            }
                        }
                    }
                } else {
                    $result[] = $child;
                }
            }
        }

        return $result;
    }
}
