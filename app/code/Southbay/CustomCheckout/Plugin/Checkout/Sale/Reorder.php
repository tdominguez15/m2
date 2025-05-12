<?php

namespace Southbay\CustomCheckout\Plugin\Checkout\Sale;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\DataObject;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;

class Reorder
{
    private $log;

    private $orderFactory;

    private $cartRepository;

    private $southbay_helper;

    /**
     * @var ConfigStoreRepository
     */
    private $configStoreRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(\Psr\Log\LoggerInterface      $log,
                                OrderFactory                  $orderFactory,
                                \Southbay\Product\Helper\Data $southbay_helper,
                                ConfigStoreRepository         $configStoreRepository,
                                StoreManagerInterface         $storeManager,
                                CartRepositoryInterface       $cartRepository,
                                ManagerInterface              $messageManager
    )
    {
        $this->log = $log;
        $this->orderFactory = $orderFactory;
        $this->southbay_helper = $southbay_helper;
        $this->configStoreRepository = $configStoreRepository;
        $this->storeManager = $storeManager;
        $this->cartRepository = $cartRepository;
        $this->messageManager = $messageManager;
    }

    public function afterExecute(\Magento\Sales\Model\Reorder\Reorder            $subject,
                                 \Magento\Sales\Model\Reorder\Data\ReorderOutput $result,
                                 string                                          $orderNumber,
                                 string                                          $storeId)
    {
        if (!empty($result->getErrors())) {
            $this->messageManager->addNoticeMessage(__('Uno o más productos no se encontraban con stock disponible.'));
        }
        $isAtOnce = $this->configStoreRepository->isAtOnce($storeId);
        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderNumber, $storeId);
        $items = $order->getItemsCollection();
        $map_items = [];

        foreach ($items as $item) {
            $map_items[$item->getSku()] = $item;
        }

        $cart = $result->getCart();
        $cart_items = $cart->getItems();
        $final_items = [];

        $this->log->info('old items', ['items' => $cart_items]);

        $cart->removeAllItems();

        foreach ($cart_items as $item) {
            if (isset($map_items[$item->getSku()])) {
                $order_item = $map_items[$item->getSku()];
                $info = $order_item->getProductOptionByCode('info_buyRequest');
                $this->log->info('info...', ['getProductOptions' => $order_item->getProductOptions(), 'info' => $info]);
                if (!isset($info['ignore'])) {
                    $_product = $item->getProduct();
                    $parent = $this->southbay_helper->findParentProduct($_product->getId());
                    $first_variant = $this->southbay_helper->getFirstProductVariant($parent);

                    $months = $this->southbay_helper->getMonthForDeliveryFromCurrent();
                    $season_qty = [];

                    foreach ($months as $month) {
                        if (isset($info[$month['code']])) {
                            $season_qty[$month['code']] = $info[$month['code']];
                        }
                    }
                    // TODO check to avoid using same validation 2 times
                    if($isAtOnce){
                       $season_qty = $this->checkAvailability($season_qty,$parent);
                    }
                    $first_variant->addCustomOption('season_qty', json_encode($season_qty));
                    $first_variant->setQty($info['qty']);
                    if($isAtOnce){
                        $first_variant->setQty(1);
                    }
                    //chequea que al menos 1 producto simple tenga stock, en caso contrario no agrega el first variant
                    $totales = $this->southbay_helper->getTotalFromSeasonConfigBySize($first_variant);
                    $totalQty = 0;
                    foreach ($totales as $total){
                        $totalQty += (int)($total['general']);
                    }
                    if($totalQty > 0){
                        $first_variant->setCustomqty($totalQty);
                        $final_items[] = $first_variant;
                    }

                }
            }
        }

        $this->log->info('new items', ['items' => $final_items]);
        $request = new DataObject;

        foreach ($final_items as $product) {
            $request['qty'] = $product->getQty();
            $cart->addProduct($product, $request);
        }
        $this->cartRepository->save($cart);

        $this->log->info('card!', ['id' => $cart->getId()]);

        return new \Magento\Sales\Model\Reorder\Data\ReorderOutput($cart, []);
    }

    public function  checkAvailability($season_qty, $product) {

        $stockAvailable = $this->southbay_helper->getChildrenStock($product);

        foreach ($season_qty as $seasonKey => $season) {
            foreach ($season as $size => $qty) {
                if(!isset($stockAvailable[$size])){
                    $season_qty[$seasonKey][$size]  = 0;
                }
                elseif ($qty > $stockAvailable[$size]) {
                    $season_qty[$seasonKey][$size] = $stockAvailable[$size];
                }
            }
        }

        return $season_qty;
    }
}
