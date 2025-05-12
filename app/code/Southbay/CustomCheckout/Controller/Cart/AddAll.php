<?php

namespace Southbay\CustomCheckout\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Southbay\Product\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Json;
use Psr\Log\LoggerInterface;

/**
 * Class AddAll
 *
 * Controlador para agregar múltiples productos al carrito de compras.
 *
 * @package Southbay\CustomCheckout\Controller\Cart
 */
class AddAll implements HttpPostActionInterface
{
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @var Cart
     */
    protected Cart $checkoutCart;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var Data
     */
    protected Data $southbayHelper;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonResultFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected ProductCollectionFactory $productCollectionFactory;

    /**
     * @var SectionPoolInterface
     */
    private SectionPoolInterface $sectionPool;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor de la clase.
     *
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param Context $context
     * @param Cart $checkoutCart
     * @param Data $southbayHelper
     * @param JsonFactory $jsonResultFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param SectionPoolInterface $sectionPool
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession          $checkoutSession,
        CustomerSession          $customerSession,
        Context                  $context,
        Cart                     $checkoutCart,
        Data                     $southbayHelper,
        JsonFactory              $jsonResultFactory,
        ProductCollectionFactory $productCollectionFactory,
        SectionPoolInterface     $sectionPool,
        ManagerInterface         $messageManager,
        LoggerInterface          $logger
    )
    {
        $this->context = $context;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutCart = $checkoutCart;
        $this->customerSession = $customerSession;
        $this->southbayHelper = $southbayHelper;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sectionPool = $sectionPool;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Ejecuta la acción de agregar múltiples productos al carrito.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $request = $this->context->getRequest();
        $checkedProducts = $request->getParam('checked_products');

        if (!$checkedProducts) {
            return $this->createJsonResponse(false, 'No se recibieron productos para agregar.', 0);
        }
        $productIds = array_filter(explode(',', $checkedProducts), 'is_numeric');

        if (empty($productIds)) {
            return $this->createJsonResponse(false, 'Los datos proporcionados son inválidos.', 0);
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['sku', 'price', 'name']);
        $collection->addIdFilter($productIds);
        $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
        $quoteMap = array_column($quoteItems, null, 'product_id');

        $products = $collection->getItems();
        $addedCount = 0;

        foreach ($products as $product) {
            $firstVariant = $this->southbayHelper->getFirstProductVariant($product);

            if ($firstVariant && !isset($quoteMap[$firstVariant->getId()])) {
                $addedCount++;
                try {
                    $this->checkoutCart->addProduct($firstVariant, 1);
                } catch (LocalizedException $e) {
                    $trackingCode = $this->generateTrackingCode();
                    $this->logger->error("Error al agregar productos al carrito. Code: $trackingCode", [
                        'error_message' => $e->getMessage(),
                        'product_id' => $firstVariant->getId(),
                        'customer_id' => $this->customerSession->getCustomerId(),
                        'tracking_code' => $trackingCode
                    ]);

                    return $this->createJsonResponse(false, "Hubo un error al agregar los productos. Code: $trackingCode", $addedCount);
                }
            }
        }

        $this->checkoutCart->save();

        return $this->createJsonResponse(true, 'Productos agregados con éxito.', $addedCount);
    }

    /**
     * Genera una respuesta JSON.
     *
     * @param bool $success Estado de la operación.
     * @param string $message Mensaje de respuesta.
     * @param int $total Cantidad de productos agregados.
     * @return Json
     */
    private function createJsonResponse(bool $success, string $message, int $total): Json
    {
        $result = $this->jsonResultFactory->create();
        $result->setData([
            'success' => $success,
            'message' => $message,
            'total' => $total
        ]);
        return $result;
    }

    /**
     * Genera un código de rastreo de 10 dígitos aleatorio.
     *
     * @return string
     */
    private function generateTrackingCode(): string
    {
        return (string) rand(1000000000, 9999999999);
    }
}
