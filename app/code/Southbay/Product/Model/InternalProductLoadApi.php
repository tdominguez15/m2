<?php

namespace Southbay\Product\Model;

use Southbay\Product\Api\Response\InternalProductLoadResponseInterface;
use Southbay\Product\Model\Import\ProductAttrLoader;
use Southbay\Product\Model\Import\ProductLoader;
use Southbay\Product\Model\Import\SubcategoriesLoader;
use Southbay\Product\Model\Response\InternalProductLoadResponse;

class InternalProductLoadApi implements \Southbay\Product\Api\InternalProductLoadApiInterface
{
    private $attrLoader;
    private $subcategoriesLoader;
    private $productLoader;


    public function __construct(ProductAttrLoader   $attrLoader,
                                SubcategoriesLoader $subcategoriesLoader,
                                ProductLoader       $productLoader)
    {
        $this->attrLoader = $attrLoader;
        $this->subcategoriesLoader = $subcategoriesLoader;
        $this->productLoader = $productLoader;
    }

    /**
     * @param mixed $data
     * @return InternalProductLoadResponseInterface
     */
    public function save($data)
    {
        $status = 'success';

        $type = $data['type'];

        switch ($type) {
            case 'products':
            {
                $this->productLoader->load($data);
                break;
            }
            case 'update':
            {
                $this->productLoader->updateProduct($data);
                break;
            }
        }

        $response = new InternalProductLoadResponse();
        $response->setStatus($status);

        return $response;
    }
}
