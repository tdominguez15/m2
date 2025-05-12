<?php

namespace Southbay\CustomCheckout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Layer\Resolver;


class DownloadCategoryStock extends Template
{
    protected $urlBuilder;
    protected $layerResolver;

    public function __construct(
        Template\Context $context,
        UrlInterface     $urlBuilder,
        Resolver         $layerResolver,
        array            $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->layerResolver = $layerResolver;
        parent::__construct($context, $data);
    }

    public function getDownloadUrl()
    {
        $layer = $this->layerResolver->get();
        $currentCategory = $layer->getCurrentCategory();

        $url = $this->urlBuilder->getUrl('southbay_custom_checkout/cart/downloadcategorystock');

        $params = [
            'category_id' => $currentCategory->getId()
        ];


        return $url . '?' . http_build_query($params);
    }
}
