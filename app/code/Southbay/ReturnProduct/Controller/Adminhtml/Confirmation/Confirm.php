<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Confirmation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class Confirm extends Action
{
    /**
     * @var Rawfactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    private $_context;
    private $log;
    private $return_product_repository;
    private $sapRtvRequest;

    public function __construct(
        Context                                          $context,
        Rawfactory                                       $resultRawFactory,
        LayoutFactory                                    $layoutFactory,
        SouthbayReturnProductRepository                  $return_product_repository,
        \Southbay\ReturnProduct\Helper\SendSapRtvRequest $sapRtvRequest,
        \Psr\Log\LoggerInterface                         $log
    )
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->log = $log;
        $this->_context = $context;
        $this->return_product_repository = $return_product_repository;
        $this->sapRtvRequest = $sapRtvRequest;
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $params = $this->_context->getRequest()->getParams();
        $this->log->debug('Saving confirmations', ['p' => $params]);

        $ids = [];

        if (isset($params['id'])) {
            $ids = [$params['id']];
        } else if (isset($params['selected']) && !empty($params['selected'])) {
            $ids = $params['selected'];
        } else if (isset($params['excluded']) && $params['excluded'] == 'false') {
            $collection = $this->return_product_repository->getPendingConfirmationDataProviderCollection();
            $ids = $collection->getAllIds();
        }

        if (!empty($ids)) {
            $ok = false;

            foreach ($ids as $id) {
                $item = $this->return_product_repository->findById($id);
                if (!is_null($item)) {
                    try {
                        $result = $this->sapRtvRequest->send($id);
                        if ($result) {
                            $this->return_product_repository->markAsConfirmed($item);
                            $ok = true;
                        } else {
                            $this->messageManager->addErrorMessage(__('Error intentando confirmar la devolucion #%1', $id));
                            $ok = false;
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('No fue posible confirmar la devolución #') . $id);
                        $this->log->error('Error', ['e' => $e]);
                    }
                }
            }

            if ($ok) {
                $this->messageManager->addSuccess(__('Devoluciones confirmadas'));
            }
        }

        return $redirect->setPath('*/*/');
    }
}
