<?php

namespace Southbay\Product\Controller\Adminhtml\ProductExclusion;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Save extends Action
{
    protected $productExclusionFactory;
    protected $productRepository;
    protected $storeManager;

    public function __construct(
        Action\Context $context,
        \Southbay\Product\Model\ProductExclusionFactory $productExclusionFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->productExclusionFactory = $productExclusionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();


        $textData = isset($params['fields']['SKUs']) ? trim($params['fields']['SKUs']) : '';
        $storeId = isset($params['fields']['store_id']) ? (int)$params['fields']['store_id'] : 0;

        if ($storeId === 0) {
            $this->messageManager->addErrorMessage(__('Store ID is missing or invalid.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        if (empty($textData)) {
            $this->messageManager->addErrorMessage(__('Text field is empty.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        $lines = explode("\n", $textData);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines);
        $count = 0;
        try {
            foreach ($lines as $sku) {
                try {
                    $product = $this->productRepository->get($sku, false, $storeId);

                    if (!$product || !$product->getId()) {
                        throw new \Magento\Framework\Exception\NoSuchEntityException(
                            __("Product with SKU %1 does not exist in the specified store.", $sku)
                        );
                    }

                    $productExclusion = $this->productExclusionFactory->create();
                    $productExclusion->setData([
                        'sku' => $sku,
                        'store' => $storeId,
                        'product_id' => $product->getId()
                    ]);
                    $productExclusion->save();
                    $count++;
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->messageManager->addErrorMessage(__('No existen productos con SKU %1 en la tienda especificada.', $sku));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Unexpected error occurred while saving product with SKU %1: %2', $sku, $e->getMessage()));
                }
            }

           if($count >0){
               $this->messageManager->addSuccessMessage(__('Se guardaron: %1 productos', $count));
           }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Error al guardar informacion: %1', $e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error inesperado: %1', $e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
