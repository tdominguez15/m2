<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Approval\Grid\Column;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApprovalUsersRepository;

class PendingApprovalUsersRenderer extends AbstractRenderer
{
    private $southbayReturnFinancialApprovalUsersRepository;

    public function __construct(Context                                        $context,
                                SouthbayReturnFinancialApprovalUsersRepository $southbayReturnFinancialApprovalUsersRepository,
                                array                                          $data = [])
    {
        $this->southbayReturnFinancialApprovalUsersRepository = $southbayReturnFinancialApprovalUsersRepository;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $require_all_members = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_REQUIRE_ALL_MEMBERS);

        if ($require_all_members) {
            $total_pending = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_TOTAL_PENDING_APPROVALS);

            if ($total_pending > 0) {
                $return_product_id = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_RETURN_ID);
                $users = $this->southbayReturnFinancialApprovalUsersRepository->getPendingUsers($return_product_id);
                return implode(',', $users);
            }
        }

        return '';
    }
}
