<?php

namespace Southbay\CustomCheckout\Observer\Frontend;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddAllSizeBeforeCard implements ObserverInterface
{
    protected $cart;
    protected $productRepository;
    protected $configurableProductType;
    protected $log;
    protected $southbay_helper;

    public function __construct(
        \Magento\Checkout\Model\Cart                    $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ConfigurableProductType                         $configurableProductType,
        \Southbay\Product\Helper\Data                   $southbay_helper,
        \Psr\Log\LoggerInterface                        $log

    )
    {
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->configurableProductType = $configurableProductType;
        $this->log = $log;
        $this->southbay_helper = $southbay_helper;
    }

    public function execute(EventObserver $observer)
    {
        $info = $observer->getEvent()->getData('info');

        // $this->log->info('AddAllSizeBeforeCard...', ['info' => $info]);

        if ($info && isset($info['form_key'])) {
            /**
             * @var \Magento\Catalog\Model\Product $product
             */
            $product = $observer->getEvent()->getData('product');
            //verificar cuando se setea from_import
            //      if (isset($info['from_import']) && $info['from_import']) {
            if (isset($info['qty'])) {
                $product->setQty(intval($info['qty']));
            }

            // $this->log->info('AddAllSizeBeforeCard. ENTER', ['id' => $product->getId(), 'qty' => $product->getQty()]);

            if (isset($info['ignore']) && $info['ignore']) {
                $product->setQty(0);
                $this->log->info('ignorando producto...');
                return;
            }

            $items = $this->cart->getItems();
            $old_season_config = [];

            foreach ($items as $item) {
                if ($item->getProduct()->getId() == $product->getId()) {
                    $old_season_config = $this->southbay_helper->getProductSeasonConfig($item->getProduct());
                    $this->cart->getQuote()->removeItem($item->getId());
                    break;
                }
            }

            if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                // $this->log->info('add info season...');
                $months = $this->southbay_helper->getMonthForDeliveryFromCurrent();

                $season_qty = [];
                $qtyTotal = 0;
                foreach ($months as $month) {
                    if (isset($info[$month['code']])) {
                        $season_qty[$month['code']] = $info[$month['code']];
                        $qtyTotal += array_sum($info[$month['code']]);
                    }
                }

                // $this->log->debug('before', ['season_qty' => $season_qty]);

                if (empty($season_qty) && !empty($old_season_config)) {
                    $season_qty = $old_season_config;
                }

                // $this->log->info('season_qty', ['season_qty' => $season_qty]);
                $product->addCustomOption('season_qty', json_encode($season_qty));
                $product->setCustomqty($qtyTotal);
            }
        }
    }
}
