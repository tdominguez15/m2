<?php

namespace Southbay\ReturnProduct\Block\Adminhtml\Approval\Grid\Column;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApprovalUsersRepository;

class ApprovalUsersRenderer extends AbstractRenderer
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
        $ok = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_APPROVED);
        $last_user_name = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_USER_NAME);

        if ($require_all_members) {
            $total = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_TOTAL_APPROVALS);

            if ($total > 0) {
                $return_product_id = $row->getData(\Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval::ENTITY_RETURN_ID);
                $response = $this->southbayReturnFinancialApprovalUsersRepository->getApprovalsUsersResponse($return_product_id);

                $result = [];

                foreach ($response as $item) {
                    $result[] = $item['username'] . ': ' . ($item['ok'] ? __('Si') : __('No'));
                }

                return implode(', ', $result);
            }
        }

        return $last_user_name . ': ' . ($ok ? __('Si') : __('No'));
    }
}
