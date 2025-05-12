<?php

namespace Southbay\ApproveOrders\Plugin;

use Magento\InventoryApi\Model\IsProductAssignedToStockInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryConfiguration\Model\GetLegacyStockItem;
use Magento\InventoryConfiguration\Model\StockItemConfigurationFactory;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForSkuInterface;

class GetStockItemConfiguration implements \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface
{
    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

    /**
     * @var StockItemConfigurationFactory
     */
    private $stockItemConfigurationFactory;

    /**
     * @var IsProductAssignedToStockInterface
     */
    private $isProductAssignedToStock;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var IsSourceItemManagementAllowedForSkuInterface
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @param GetLegacyStockItem $getLegacyStockItem
     * @param StockItemConfigurationFactory $stockItemConfigurationFactory
     * @param IsProductAssignedToStockInterface $isProductAssignedToStock
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku
     */
    public function __construct(
        GetLegacyStockItem                           $getLegacyStockItem,
        StockItemConfigurationFactory                $stockItemConfigurationFactory,
        IsProductAssignedToStockInterface            $isProductAssignedToStock,
        DefaultStockProviderInterface                $defaultStockProvider,
        IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku
    )
    {
        $this->getLegacyStockItem = $getLegacyStockItem;
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
        $this->isProductAssignedToStock = $isProductAssignedToStock;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): StockItemConfigurationInterface
    {
        if (\Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID == $stockId) {
            $stockItem = $this->getLegacyStockItem->execute($sku);
            $stockItem->setManageStock(0);
            $stockItem->setUseConfigManageStock(0);
        } else {
            if ($this->defaultStockProvider->getId() !== $stockId
                && true === $this->isSourceItemManagementAllowedForSku->execute($sku)
                && false === $this->isProductAssignedToStock->execute($sku, $stockId)) {
                throw new SkuIsNotAssignedToStockException(
                    __('The requested sku is not assigned to given stock.')
                );
            }
            $stockItem = $this->getLegacyStockItem->execute($sku);
        }

        return $this->stockItemConfigurationFactory->create(
            [
                'stockItem' => $stockItem
            ]
        );
    }
}
