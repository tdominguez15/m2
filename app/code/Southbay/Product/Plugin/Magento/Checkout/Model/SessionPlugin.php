<?php

namespace Southbay\Product\Plugin\Magento\Checkout\Model;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;

class SessionPlugin
{
    /*
    public function beforeGetLastRealOrder($session)
    {
        $log = $this->getLog();
        $log->info('beforeGetLastRealOrder....', ['args' => func_get_args()]);
        // return random_int(100, 100000);
        return null;
    }
    */

    public function afterGetLastRealOrder(\Magento\Checkout\Model\Session\Interceptor $session,\Magento\Sales\Model\Order $order)
    {
        $log = $this->getLog();
        $log->info('afterGetLastRealOrder....', ['args' => func_get_args()]);
        // return random_int(100, 100000);
        return $order;
    }

    private function getLog()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
    }
}
