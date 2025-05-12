<?php

namespace Southbay\CustomCheckout\Controller\Quote;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Model\Quote\ItemFactory;

/**
 *
 * Controlador para manejar la cantidad personalizada de un artículo en la cotización, esto es debido a que los item solo tienen seteado 1 en el qty, en cambio este valor puede ser utilizado en diferentes template, por ejemplo summary.
 */
class Customqty extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * Customqty constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ItemFactory $quoteItemFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ItemFactory $quoteItemFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $itemId = $this->getRequest()->getParam('item_id');

        if ($itemId) {
            try {
                $quoteItem = $this->quoteItemFactory->create()->load($itemId);

                if ($quoteItem->getId()) {
                    return $result->setData([
                        'success' => true,
                        'customqty' => (int) $quoteItem->getCustomqty()
                    ]);
                } else {
                    return $result->setData([
                        'success' => false,
                        'message' => __('Quote item not found.')
                    ]);
                }
            } catch (\Exception $e) {
                return $result->setData([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return $result->setData([
                'success' => false,
                'message' => __('Item ID is missing.')
            ]);
        }
    }
}
