<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\ControlQa;

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
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQaRepository $repository,
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
        $this->log->debug('Saving control qa:', ['params' => $params]);

        $southbay_return_product_id = $params['fields']['detail']['southbay_return_id'];
        $southbay_return_control_qa_items = $params['fields']['items_fieldset']['southbay_return_control_qa_items'];
        $southbay_return_control_qa_items = json_decode($southbay_return_control_qa_items, true);

        if (isset($params['fields']['detail']['southbay_return_control_qa_id'])) {
            $southbay_return_control_qa_id = $params['fields']['detail']['southbay_return_control_qa_id'];
        } else {
            $southbay_return_control_qa_id = null;
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        $return_product = $this->return_product_repository->findById($southbay_return_product_id);

        if (is_null($return_product)) {
            $this->messageManager->addError(__('No existe la devolución #') . $southbay_return_product_id);
            return $resultRedirect->setPath('*/*/');
        } else if (is_null($southbay_return_control_qa_id)) {
            if (!$this->return_product_repository->availableForControlQa($return_product)) {
                $this->messageManager->addError(__('No se puede procesar la devolución #' . $southbay_return_product_id));
                return $resultRedirect->setPath('*/*/');
            }
        } else if (!$this->return_product_repository->availableForEditControlQa($return_product)) {
            $this->messageManager->addError(__('No se puede modificar la devolución #' . $southbay_return_product_id));
            return $resultRedirect->setPath('*/*/');
        }

        $items = [];

        foreach ($southbay_return_control_qa_items as $key => $item_data) {
            $item_data['qty_real'] = intval($item_data['qty_real']);
            $item_data['qty_accepted'] = intval($item_data['qty_accepted']);
            $item_data['qty_return'] = intval($item_data['qty_return']);
            if (!isset($item_data['qty_rejected'])) {
                $item_data['qty_rejected'] = 0;
            } else {
                $item_data['qty_rejected'] = intval($item_data['qty_rejected']);
            }
            $items[] = $item_data;
        }

        $data = [
            'control_qa_id' => $southbay_return_control_qa_id,
            'return_id' => $southbay_return_product_id,
            'user_id' => $this->authSession->getUser()->getId(),
            'user_name' => $this->authSession->getUser()->getUserName(),
            'items' => $items
        ];

        $result = $this->repository->save($return_product, $data);

        if ($result === false) {
            $this->messageManager->addError(__('No se pudo guardar los cambios. La devolución ya fue procesada anteriormente'));
            return $resultRedirect->setPath('*/*/');
        } else {
            $this->messageManager->addSuccess(__('Control de calidad guardado exitosamente'));
            return $resultRedirect->setPath('*/*/');
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
