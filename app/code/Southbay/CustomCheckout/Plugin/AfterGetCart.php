<?php

namespace Southbay\CustomCheckout\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Cart\CartTotalRepository;
use Southbay\Product\Helper\Data as ProductHelper;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;

class AfterGetCart
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var ConfigStoreRepository
     */
    protected $configStoreRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Constructor
     *
     * @param CartRepositoryInterface $cartRepository
     * @param ProductHelper $productHelper
     * @param ConfigStoreRepository $configStoreRepository
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductHelper $productHelper,
        ConfigStoreRepository $configStoreRepository,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->productHelper = $productHelper;
        $this->configStoreRepository = $configStoreRepository;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    /**
     * @param CartTotalRepository $subject
     * @param TotalsInterface $result
     * @param int $cartId
     * @return TotalsInterface
     */
    public function afterGet(CartTotalRepository $subject, TotalsInterface $result, $cartId): TotalsInterface
    {
        $isAtOnce = $this->configStoreRepository->isAtOnce($this->storeManager->getStore()->getId());
        if(!$isAtOnce ){
            return $result;
        }

        $quote = $this->cartRepository->get($cartId);
        if (!$quote) {
            return $result;
        }
        $quoteItems = $quote->getAllItems();
        if (empty($quoteItems)) {
            return $result;
        }
        $customGrandTotal = 0;
        $quote_map_products = [];
        foreach ($quoteItems as $item) {
            $product = $item->getProduct();
            if ($product) {
                $quote_map_products[$product->getId()] = $product;
            } else {
                $this->messageManager->addErrorMessage(__('Product not found for item ID: %1', $item->getId()));
            }
        }
        foreach ($quoteItems as $item) {

            $quote_product = $quote_map_products[$item->getProductId()];
            $qTyTotales = $this->productHelper->getTotalFromSeasonConfigBySize($quote_product);
            foreach ($qTyTotales as $qty)
                $customGrandTotal += $item->getPrice() * $qty['general'];
        }

        $result->setSubtotal($customGrandTotal);
        $result->setBaseSubtotal($customGrandTotal);
        $result->setGrandTotal($customGrandTotal);
        $result->setBaseGrandTotal($customGrandTotal);

        return $result;
    }
}


