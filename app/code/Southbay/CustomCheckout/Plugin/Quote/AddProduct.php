<?php

namespace Southbay\CustomCheckout\Plugin\Quote;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Quote\Model\Quote;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote\Item\Processor as ItemProcessor;

class AddProduct
{
    /**
     * @var Factory
     */
    private $objectFactory;

    /**
     * @var ItemProcessor
     */
    private $itemProcessor;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * Constructor.
     *
     * @param Factory $objectFactory
     * @param ItemProcessor $itemProcessor
     * @param EventManager $eventManager
     */
    public function __construct(
        Factory $objectFactory,
        ItemProcessor $itemProcessor,
        EventManager $eventManager
    ) {
        $this->objectFactory = $objectFactory;
        $this->itemProcessor = $itemProcessor;
        $this->eventManager = $eventManager;
    }

    /**
     * Around plugin for addProduct method.
     *
     * @param Quote $subject
     * @param callable $proceed
     * @param Product|mixed $product
     * @param float|\Magento\Framework\DataObject|null $request
     * @param string|null $processMode
     * @throws LocalizedException
     */
    public function aroundAddProduct(Quote $subject, callable $proceed, Product $product, $request = null, $processMode = AbstractType::PROCESS_MODE_FULL)
    {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }
        if (!$request instanceof \Magento\Framework\DataObject) {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        if (!$product->isSalable()) {
            throw new LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates) || $cartCandidates instanceof Phrase) {
            return (string)$cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $parentItem = null;
        $errors = [];
        $items = [];
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);


            $item = $subject->getItemByProduct($candidate);
            if (!$item) {
                $item = $this->itemProcessor->init($candidate, $request);
                $item->setQuote($subject);
                $item->setOptions($candidate->getCustomOptions());
                $item->setProduct($candidate);
                $item->setCustomqty($candidate->getCustomqty());
                $subject->addItem($item);
            }
            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }

            $this->itemProcessor->prepare($item, $request, $candidate);

            // collect errors instead of throwing first one
            if ($item->getHasError() && false) {
                $subject->deleteItem($item);
                foreach ($item->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        // filter duplicate messages
                        $errors[] = $message;
                    }
                }
                break;
            }
        }
        if (!empty($errors)) {
    //        throw new LocalizedException(__(implode("\n", $errors)));
        }

        $this->eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);
        return $parentItem;
    }
}
