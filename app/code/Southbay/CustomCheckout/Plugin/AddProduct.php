<?php

namespace Southbay\CustomCheckout\Plugin;

use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Store\Model\StoreManagerInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;


class AddProduct
{
    /**
     * @var ConfigStoreRepository
     */
    protected $configStoreRepository;

    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AddProduct constructor.
     *
     * @param ConfigStoreRepository $configStoreRepository
     * @param CheckoutCart $cart
     */
    public function __construct(
        ConfigStoreRepository $configStoreRepository,
        CheckoutCart $cart,
        StoreManagerInterface $storeManager,
    )
    {
        $this->configStoreRepository = $configStoreRepository;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
    }

    /**
     * Plugin beforeAddProduct
     *
     * @param CheckoutCart $subject
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param array|int|\Magento\Framework\DataObject|null $requestInfo
     * @return array
     */
    public function beforeAddProduct(CheckoutCart $subject, $productInfo, $requestInfo = null): array
    {
        $store = $this->configStoreRepository->findByStoreId($this->storeManager->getStore()->getId());
        if($store->getId() && $store->getFunctionCode() === ConfigStoreInterface::FUNCTION_CODE_AT_ONCE && isset( $requestInfo['qty'])) {
            $requestInfo['qty'] = 1;
        }

        return [$productInfo, $requestInfo];
    }
}
