<?php

namespace Southbay\ReturnProduct\Model;

class PendingConfirmationReturnProductDataProvider extends ReturnProductDataProvider
{
    protected function createCollection()
    {
        return $this->returnProductRepository->getPendingConfirmationDataProviderCollection();
    }
}
