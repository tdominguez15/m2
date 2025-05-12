<?php

namespace Southbay\ReturnProduct\Model;

class ArchivedReturnProductDataProvider extends ReturnProductDataProvider
{
    protected function createCollection()
    {
        return $this->returnProductRepository->getArchivedDataProviderCollection();
    }
}
