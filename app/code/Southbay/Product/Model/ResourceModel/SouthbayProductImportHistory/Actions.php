<?php

namespace Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory;

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

    protected function urlBase()
    {
        return 'southbay_order_entry/product';
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'retry' => [
                        'href' => $this->backendUrl->getUrl($this->urlBase() . '/retry', ['id' => $item['season_import_id']]),
                        'label' => __('Reintentar')
                    ],
                    'download' => [
                        'href' => $this->backendUrl->getUrl($this->urlBase() . '/download', ['id' => $item['season_import_id']]),
                        'label' => __('Descargar archivo')
                    ],
                    'remove' => [
                        'href' => $this->backendUrl->getUrl($this->urlBase() . '/delete', ['id' => $item['season_import_id']]),
                        'label' => __('Eliminar')
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
