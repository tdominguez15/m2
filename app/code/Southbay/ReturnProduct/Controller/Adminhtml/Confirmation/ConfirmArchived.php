<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Confirmation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Southbay\ReturnProduct\Model\ArchivedReturnProductDataProvider;
use Southbay\ReturnProduct\Model\PendingConfirmationReturnProductDataProvider;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class ConfirmArchived extends Action
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

        if (isset($params['selected']) && !empty($params['selected'])) {
            $ids = $params['selected'];
        } else if (isset($params['excluded']) && $params['excluded'] == 'false') {
            $collection = $this->return_product_repository->getArchivedDataProviderCollection();
            $ids = $collection->getAllIds();
        }

        if (!empty($ids)) {
            $this->messageManager->addSuccess('Devoluciones confirmadas');
            foreach ($ids as $id) {
                $item = $this->return_product_repository->findById($id);
                if (!is_null($item)) {
                    try {
                        $this->return_product_repository->markAsConfirmed($item);
                        $this->sapRtvRequest->send($id);
                    } catch (\Exception $e) {
                        $this->log->error('Error', ['e' => $e]);
                    }
                }
            }
        }

        return $redirect->setPath('*/*/');
    }
}
