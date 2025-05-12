<?php

namespace Southbay\CustomCustomer\Plugin;

class RedirectCustomUrl
{
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
                                                       $result)
    {
        // Apache: SetEnv SOUTHBAY_LANDING_DISABLED "on"
        // Nginx:  fastcgi_param SOUTHBAY_LANDING_DISABLED "on";
        $disable_landing = getenv('SOUTHBAY_LANDING_DISABLED', false);

        if ($disable_landing === false || strtolower($disable_landing) !== 'on') {
            $customUrl = 'landing';
            $result->setPath($customUrl);
        }

        return $result;
    }
}
