<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Approval;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;

class ApprovalBlock extends Template
{
    private $_helper;
    private $repository;
    private $backendUrl;

    public function __construct(Template\Context                    $context,
                                \Southbay\ReturnProduct\Helper\Data $helper,
                                \Magento\Backend\Model\UrlInterface $backendUrl,
                                SouthbayReturnProductRepository     $repository,
                                array                               $data = [],
                                ?JsonHelper                         $jsonHelper = null,
                                ?DirectoryHelper                    $directoryHelper = null)
    {
        $this->_helper = $helper;
        $this->repository = $repository;
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    public function getApproval()
    {
        $id = $this->getRequest()->getParam('id');

        if (!empty($id)) {
            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $item
             */
            $item = $this->repository->findPendingApprovalById($id);
            if (!is_null($item)) {
                $exchange = $this->_helper->getLastExchange($item->getCountryCode());

                if (!is_null($exchange)) {
                    $_item = $this->_helper->getApprovalPendingItem($item, $this->repository->getTypeName($item->getType()), $exchange->getExchange());
                    $_item['link'] = $this->backendUrl->getUrl('southbay_return_product/confirmation/view', ['id' => $item->getId()]);
                    return $_item;
                }
            }
        }

        return null;
    }
}
