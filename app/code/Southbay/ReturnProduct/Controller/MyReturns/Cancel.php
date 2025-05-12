<?php

namespace Southbay\ReturnProduct\Controller\MyReturns;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class Cancel implements HttpGetActionInterface
{
    private $context;
    private $repository;
    private $log;

    private $itemRepository;

    public function __construct(Context                             $context,
                                \Psr\Log\LoggerInterface            $log,
                                SouthbayReturnProductItemRepository $itemRepository,
                                SouthbayReturnProductRepository     $repository)
    {
        $this->context = $context;
        $this->log = $log;
        $this->repository = $repository;
        $this->itemRepository = $itemRepository;
    }

    public function execute()
    {
        $redirect = $this->context->getResultRedirectFactory()->create();
        $redirect->setPath('southbay_return_product/myreturns');

        $id = $this->context->getRequest()->getParam('id');

        if (empty($id)) {
            $this->context->getMessageManager()->addErrorMessage(__('No se indicó el trámite a devolver'));
            return $redirect;
        }

        $item = $this->repository->findById($id);

        if (is_null($item)) {
            $this->context->getMessageManager()->addErrorMessage(__('El trámite no existe'));
            return $redirect;
        }

        if (!$this->repository->cancelableByCustomer($item)) {
            $this->context->getMessageManager()->addErrorMessage(__('El trámite no esta disponible para su cancelación'));
            return $redirect;
        }

        $this->itemRepository->cancelReturnProduct($item->getId());
        $this->repository->markAsCancel($item);

        $this->context->getMessageManager()->addSuccessMessage(__('Trámite cancelado'));

        return $redirect;
    }
}
