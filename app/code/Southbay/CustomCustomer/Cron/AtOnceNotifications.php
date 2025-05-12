<?php

namespace Southbay\CustomCustomer\Cron;

use Southbay\CustomCustomer\Helper\SouthbayApproveOrderHelper;

class AtOnceNotifications
{
    /**
     * @var SouthbayApproveOrderHelper
     */
    protected $approveOrderHelper;

    /**
     * Constructor
     *
     * @param SouthbayApproveOrderHelper $southbayApproveOrderHelper
     */
    public function __construct(SouthbayApproveOrderHelper $approveOrderHelper)
    {
        $this->approveOrderHelper = $approveOrderHelper;
    }

    /**
     * Cronjob Description
     *
     * @return void
     */
    public function execute(): void
    {
        $this->approveOrderHelper->sendPendingNotifications();
    }
}
