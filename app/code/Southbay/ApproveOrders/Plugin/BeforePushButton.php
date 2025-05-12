<?php

namespace Southbay\ApproveOrders\Plugin;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar;
use Magento\Framework\View\Element\AbstractBlock;

class BeforePushButton
{
    /**
     * @param Toolbar $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(Toolbar $subject, AbstractBlock $context, ButtonList $buttonList): array
    {
        $request = $context->getRequest();
        if ($request->getFullActionName() == 'sales_order_view') {
            $orderId = $request->getParam('order_id');
            // $url = $this->urlBuilder->getUrl('southbay_approve_orders/order/approve', ['order_id' => $orderId]);
            $url = $context->getUrl('southbay_approve_orders/order/approve', ['order_id' => $orderId]);

            $buttonList->add(
                'southbay_approve_order',
                [
                    'label' => __('Aprobar'),
                    'class' => 'southbay_approve_order primary',
                    'onclick' => 'confirmSetLocation(\'' . __(
                            '¿Está seguro de que requiere aprobar esta orden?'
                        ) . '\', \'' . $url . '\')'
                ],
                -1
            );

        }
        return [$context, $buttonList];
    }
}
