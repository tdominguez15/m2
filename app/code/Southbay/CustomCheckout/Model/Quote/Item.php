<?php
namespace Southbay\CustomCheckout\Model\Quote;

use Magento\Quote\Model\Quote\Item as QuoteItem;

class Item extends QuoteItem
{
    /**
     * @var string
     */
    protected $_customQty;

    /**
     * @param int $customQty
     * @return $this
     */
    public function setCustomQty($customQty)
    {
        $this->_customQty = $customQty;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCustomQty()
    {
        return $this->_customQty;
    }
}
