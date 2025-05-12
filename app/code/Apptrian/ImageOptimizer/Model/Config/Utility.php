<?php
/**
 * @category  Apptrian
 * @package   Apptrian_ImageOptimizer
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License
 */
 
namespace Apptrian\ImageOptimizer\Model\Config;

use Magento\Framework\Exception\LocalizedException;

class Utility extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate and prepare data before saving config value.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        
        $pattern = '/^[\p{L}\p{N}_,;:!&#\+\*\$\?\|\'\.\-\ \/]+$/iu';
        $validator = preg_match($pattern, $value);
        
        if (!$validator) {
            $message = __(
                'Please correct Utility field: "%1".',
                $value
            );
            throw new LocalizedException($message);
        }
        
        return $this;
    }
}
