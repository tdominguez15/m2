<?php

namespace Southbay\Product\Model\ResourceModel\Season;

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
                    'download' => [
                        'href' => $this->backendUrl->getUrl('southbay_order_entry/season/downloadproductlist', ['id' => $item['season_id']]),
                        'label' => __('Descargar linea')
                    ],
                    'active' => [
                        'href' => $this->backendUrl->getUrl('southbay_order_entry/season/active', ['id' => $item['season_id']]),
                        'label' => __('Activar')
                    ],
                    'edit' => [
                        'href' => $this->backendUrl->getUrl('southbay_order_entry/season/edit', ['id' => $item['season_id']]),
                        'label' => __('Editar')
                    ],
                    'remove' => [
                        'href' => $this->backendUrl->getUrl('southbay_order_entry/season/delete', ['id' => $item['season_id']]),
                        'label' => __('Eliminar')
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
