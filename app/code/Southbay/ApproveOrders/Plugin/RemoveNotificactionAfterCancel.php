<?php

namespace Southbay\ApproveOrders\Plugin;

use Magento\Sales\Model\Order;
use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;

class RemoveNotificactionAfterCancel
{

    /**
     * @var SouthbayApproveOrderHelper
     */
    private $approveOrderHelper;

    /**
     *
     * @param SouthbayApproveOrderHelper $approveOrderHelper
     */
    public function __construct(
        SouthbayApproveOrderHelper          $approveOrderHelper
    )
    {
        $this->approveOrderHelper = $approveOrderHelper;
    }





    /**
     * @param Order $subject
     * @param $result
     */
    public function afterCancel(Order $subject, $result)
    {

        if($this->approveOrderHelper->isOrderAtOnce($subject)){
            $this->approveOrderHelper->cancelAllByOrderId($subject->getId());
        }
        return $result;
    }
}
