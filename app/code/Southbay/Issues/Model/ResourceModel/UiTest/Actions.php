<?php

namespace Southbay\Issues\Model\ResourceModel\UiTest;

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
                    'edit' => [
                        'href' => $this->backendUrl->getUrl('southbay_issues/uitest/edit', ['id' => $item['entity_id']]),
                        'label' => __('Editar')
                    ],
                    'delete' => [
                        'href' => $this->backendUrl->getUrl('southbay_issues/uitest/delete', ['id' => $item['entity_id']]),
                        'confirm' => ['title' => __('Confirmar eliminación'), 'message' => __('¿Estás seguro de que deseas eliminar este registro?')],
                        'label' => __('Eliminar')
                    ]
                ];
            }
        }
        return $dataSource;
    }
}
