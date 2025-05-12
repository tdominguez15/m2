<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductImportImgHistory;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    private $backendUrl;

    public function __construct(ContextInterface $context, \Magento\Backend\Model\UrlInterface $backendUrl, UiComponentFactory $uiComponentFactory, array $components = [], array $data = [])
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->backendUrl = $backendUrl;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'retry' => [
                        'href' => $this->backendUrl->getUrl('southbay_order_entry/image/retry', ['id' => $item['entity_id']]),
                        'label' => __('Reintentar')
                    ],
                    'remove' => [
                        'href' => $this->backendUrl->getUrl('southbay_order_entry/image/delete', ['id' => $item['entity_id']]),
                        'label' => __('Eliminar')
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
