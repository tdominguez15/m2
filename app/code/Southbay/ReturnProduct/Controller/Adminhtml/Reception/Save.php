<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Reception;

use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;
use Magento\Backend\Model\Auth\Session;

class Save extends \Magento\Backend\App\Action
{
    private $log;
    private $repository;

    private $return_product_repository;
    private $authSession;

    public function __construct(
        \Magento\Backend\App\Action\Context                                           $context,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnReceptionRepository $repository,
        SouthbayReturnProductRepository                                               $return_product_repository,
        Session                                                                       $authSession,
        \Psr\Log\LoggerInterface                                                      $log
    )
    {
        $this->log = $log;
        $this->repository = $repository;
        $this->return_product_repository = $return_product_repository;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->log->debug('Saving reception:', ['params' => $params]);

        $southbay_return_product_id = $params['fields']['detail']['southbay_return_id'];
        $total_packages = $params['fields']['detail']['southbay_return_reception_total_packages'];

        if (isset($params['fields']['detail']['southbay_return_reception_id'])) {
            $southbay_return_reception_id = $params['fields']['detail']['southbay_return_reception_id'];
        } else {
            $southbay_return_reception_id = null;
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        $return_product = $this->return_product_repository->findById($southbay_return_product_id);

        if (is_null($return_product)) {
            $this->messageManager->addError(__('No existe la devolución #') . $southbay_return_product_id);
            return $resultRedirect->setPath('*/*/new');
        } else {
            if($total_packages <= 0) {
                $this->messageManager->addError(__('Debe ingresar un valor mayor que cero'));
                return $resultRedirect->setPath('*/*/new');
            }

            if (is_null($southbay_return_reception_id)) {
                if ($this->return_product_repository->availableForReception($return_product)) {
                    $data = [
                        'return_id' => $southbay_return_product_id,
                        'user_id' => $this->authSession->getUser()->getId(),
                        'user_name' => $this->authSession->getUser()->getUserName(),
                        'total_packages' => $total_packages
                    ];

                    $result = $this->repository->save($return_product, $data);

                    if ($result === false) {
                        $this->messageManager->addError(__('No se pudo guardar la recepción. La devolución ya fue procesada anteriormente'));

                        return $resultRedirect->setPath('*/*/');
                    } else {
                        $this->messageManager->addSuccess(__('Recepción guardada exitosamente'));
                        return $resultRedirect->setPath('*/*/');
                    }
                } else {
                    $this->messageManager->addError(__('No es posible recepcionar la devolución #' . $southbay_return_product_id));
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $reception = $this->repository->findById($southbay_return_reception_id);

                if (is_null($reception)) {
                    $this->messageManager->addError(__('No existe la recepción que intenta modificar'));
                    return $resultRedirect->setPath('*/*/');
                } else {
                    $reception->setTotalPackages($total_packages);
                    $this->repository->updateTotalPackages(
                        $reception->getId(),
                        $total_packages,
                        $this->authSession->getUser()->getId(),
                        $this->authSession->getUser()->getUserName());

                    $this->messageManager->addSuccess(__('Recepción guardada exitosamente'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
        }
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
