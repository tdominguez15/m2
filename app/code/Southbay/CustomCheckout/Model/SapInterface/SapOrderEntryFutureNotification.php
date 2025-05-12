<?php

namespace Southbay\CustomCheckout\Model\SapInterface;

use Southbay\CustomCheckout\Model\Report\SouthbayFutureOrderEntry;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;
use Southbay\CustomCustomer\Model\ConfigStoreRepository;
use Southbay\CustomCustomer\Model\MapCountryRepository;
use Southbay\Product\Model\ProductExclusionRepository;
use Southbay\Product\Model\ResourceModel\SeasonRepository;
use Southbay\ReturnProduct\Helper\SendSapRequest;

class SapOrderEntryFutureNotification
{
    private $collectionFactory;
    private $log;
    private $resource;

    private $seasonRepository;
    private $sendSapRequest;
    private $futureOrderEntry;

    private $configStoreRepository;
    private $mapCountryRepository;

    private $detail = [];

    public function __construct(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
                                \Magento\Framework\App\ResourceConnection                  $resource,
                                SeasonRepository                                           $seasonRepository,
                                SendSapRequest                                             $sendSapRequest,
                                SouthbayFutureOrderEntry                                   $futureOrderEntry,
                                ConfigStoreRepository                                      $configStoreRepository,
                                MapCountryRepository                                       $mapCountryRepository,
                                \Psr\Log\LoggerInterface                                   $log)
    {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->seasonRepository = $seasonRepository;
        $this->sendSapRequest = $sendSapRequest;
        $this->futureOrderEntry = $futureOrderEntry;
        $this->configStoreRepository = $configStoreRepository;
        $this->mapCountryRepository = $mapCountryRepository;
        $this->log = $log;
    }

    public function sendByIncrementOrder($id, $sap_zone = null, $sap_channel = null, $sap_doc = null)
    {
        return $this->sendOrderByFieldName($id, 'increment_id', $sap_zone, $sap_channel, $sap_doc);
    }

    public function sendOrderByFieldName($id, $field_name = 'entity_id', $sap_zone = null, $sap_channel = null, $sap_doc = null)
    {
        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter($field_name, ['eq' => $id]);
        $collection->addFieldToFilter('status', ['in' => ['pending', 'processing']]);

        if ($collection->count() == 0) {
            $this->log->debug('La orden no esta disponible para ser notificada', ['id' => $id]);
            return false;
        }

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $collection->getFirstItem();
        $config_store = $this->configStoreRepository->getById($order->getStoreId());

        if (is_null($config_store)) {
            $this->log->debug('No existe configuracion para tienda', ['id' => $id, 'store_id' => $order->getStoreId()]);
            return false;
        }

        return $this->sendOrder($order, $config_store, $sap_zone, $sap_channel, $sap_doc);
    }

    public function sendOrder($order, $config_store, $sap_zone = null, $sap_channel = null, $sap_doc = null)
    {
        $this->detail = [];
        $map_country = $this->mapCountryRepository->findByCountryCode($config_store->getCountryCode());

        if (is_null($map_country)) {
            $this->log->debug('No existe configuracion para el pais', [
                'id' => $order->getId(),
                'store_id' => $order->getStoreId(),
                'country_code' => $config_store->getCountryCode()
            ]);
            return false;
        }

        if (is_null($sap_channel) && !empty($map_country->getSapChannel())) {
            $sap_channel = $map_country->getSapChannel();
        }

        if (is_null($sap_zone) && !empty($map_country->getSapZone())) {
            $sap_zone = $map_country->getSapZone();
        }

        $this->futureOrderEntry->loadProductsCacheForOrders([$order->getId()], $order->getStoreId());

        if ($config_store->getFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE) {
            if (is_null($sap_doc) && !empty($map_country->getSapAtOnceDoc())) {
                $sap_doc = $map_country->getSapAtOnceDoc();
            } else if (is_null($sap_doc) || empty($map_country->getSapAtOnceDoc())) {
                $this->log->error('Error sap document not set', [
                    'id' => $order->getId(),
                    'store_id' => $order->getStoreId(),
                    'country_code' => $config_store->getCountryCode()
                ]);

                throw new \Exception(__('No hay un documento configurada para las ordenes de at once'));
            }

            $items = $this->futureOrderEntry->getSeasonDetail(
                $order,
                $sap_zone, $sap_channel, $sap_doc,
                $this->resource->getConnection(),
                [
                    'atonce' => ""
                ]
            );
        } else if ($config_store->getFunctionCode() === ConfigStoreInterface::FUNCTION_CODE_FUTURES) {
            $season = $this->seasonRepository->findById($order->getExtOrderId());

            if (is_null($season)) {
                $this->log->debug('No existe la temporada asociada a la orden', [
                    'id' => $order->getId(),
                    'store_id' => $order->getStoreId(),
                    'country_code' => $config_store->getCountryCode(),
                    'season_id' => $order->getExtOrderId()
                ]);
                return false;
            }

            if (is_null($sap_doc) && !is_null($map_country->getSapFutureDoc())) {
                $sap_doc = $map_country->getSapFutureDoc();
            } else if (is_null($sap_doc) || empty($map_country->getSapAtOnceDoc())) {
                $this->log->error('Error sap document not set', [
                    'id' => $order->getId(),
                    'store_id' => $order->getStoreId(),
                    'country_code' => $config_store->getCountryCode()
                ]);

                throw new \Exception(__('No hay un documento configurada para las ordenes de futuros'));
            }

            $items = $this->futureOrderEntry->getSeasonDetail(
                $order,
                $sap_zone, $sap_channel, $sap_doc,
                $this->resource->getConnection(),
                [
                    'month_delivery_date_1' => $season->getData('month_delivery_date_1'),
                    'month_delivery_date_2' => $season->getData('month_delivery_date_2'),
                    'month_delivery_date_3' => $season->getData('month_delivery_date_3')
                ]
            );
        } else {
            $this->log->debug('Codigo de funcion no implementado', [
                'id' => $order->getId(),
                'store_id' => $order->getStoreId(),
                'country_code' => $config_store->getCountryCode(),
                'store_function_code' => $config_store->getFunctionCode()
            ]);
            return false;
        }

        $date = strtotime($order->getCreatedAt());
        $group = [];

        foreach ($items as $item) {
            /*
            if ($item['country'] == 'AR') {
                if (in_array($item['sku'], $this->exclude_sku)) {
                    $this->log->debug('Exclude sku: ' . $item['sku'], ['order_id' => $item['id'], 'increment_id' => $item['customer_order_id']]);
                    continue;
                }

                $generic = substr($item['variant'], 0, 8);

                if (in_array($generic, $this->exclude_variant)) {
                    $this->log->debug(
                        'Exclude variant: ' . $item['sku'], [
                        'variant' => $item['variant'],
                        'generic' => $generic,
                        'order_id' => $item['id'],
                        'increment_id' => $item['customer_order_id']
                    ]);
                    continue;
                }
            }
            */

            $key = $item['department'] . '-' . $item['flow'];
            if (!isset($group[$key])) {
                $season_date = strtotime($item['flow']);
                $group[$key] = [
                    'id' => $item['id'],
                    'customer_order_id' => $item['customer_order_id'],
                    'country_code' => $item['country'],
                    'doc' => $item['doc'],
                    'channel' => $item['channel'],
                    'observation' => trim($order->getCustomerNote() ?? ''),
                    'sold_to' => $item['so'],
                    'ship_to' => $item['dm'],
                    'date' => date('d.m.Y', $date),
                    'season_date' => ($config_store->getFunctionCode() == ConfigStoreInterface::FUNCTION_CODE_AT_ONCE ? "" : date('d.m.Y', $season_date)),
                    'items' => []
                ];
            }

            $_data = $group[$key];

            if (empty($item['variant'])) {
                throw new \Exception(__('No fue posible obtener la variante asociada al producto %/%', $item['sku'], $item['size']));
            }

            if (intval($item['qty']) <= 0) {
                throw new \Exception(__('No hay unidades para el producto %/%', $item['sku'], $item['size']));
            }

            $_data['items'][] = [
                'variant' => $item['variant'],
                'qty' => $item['qty']
            ];

            $group[$key] = $_data;
        }

        $total = 0;

        foreach ($group as $data) {
            $this->log->debug('sending request: ', [$data]);
            $ok = $this->sendSapRequest->sapOrderRequest($data);

            if ($ok) {
                $total++;
            }
        }

        if ($total > 0) {
            $docs = $this->sendSapRequest->getDocs($order->getId(), 'futures');
            foreach ($docs as $doc) {
                $this->detail[] = [
                    'id' => $doc->getId(),
                    'status' => $doc->getStatus(),
                    'end' => $doc->getEnd()
                ];
            }
        }

        return $total;
    }

    public function getDetail()
    {
        return $this->detail;
    }
}
