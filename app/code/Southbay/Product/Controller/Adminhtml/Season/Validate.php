<?php

namespace Southbay\Product\Controller\Adminhtml\Season;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;

class Validate extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultFactory;

    private $collectionFactory;

    private $timezone;

    private $log;

    private $configStoreRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                        $context,
        JsonFactory                                                    $resultFactory,
        TimezoneInterface                                              $timezone,
        \Southbay\Product\Model\ResourceModel\Season\CollectionFactory $collectionFactory,
        \Southbay\CustomCustomer\Model\ConfigStoreRepository           $configStoreRepository,
        \Psr\Log\LoggerInterface                                       $log
    )
    {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->collectionFactory = $collectionFactory;
        $this->timezone = $timezone;
        $this->log = $log;
        $this->configStoreRepository = $configStoreRepository;
    }

    public function execute()
    {
        $result = $this->resultFactory->create();
        $result_data = [
            'error' => false,
            'messages' => []
        ];

        $params = $this->getRequest()->getParams();

        $id = $params['fields']['season_id'] ?? null;
        $code = $params['fields']['season_code'];
        $type = $params['fields']['season_type_code'];
        $country_code = $params['fields']['season_country_code'];
        $str_start_at = $params['fields']['season_start_at'];
        $str_end_at = $params['fields']['season_end_at'];
        $str_month_1 = $params['fields']['month_delivery_date_1'];
        $str_month_2 = $params['fields']['month_delivery_date_2'];
        $str_month_3 = $params['fields']['month_delivery_date_3'];

        $start_at = $this->timezone->date($str_start_at);
        $end_at = $this->timezone->date($str_end_at);

        $month_1 = $this->timezone->date($str_month_1);
        $month_2 = $this->timezone->date($str_month_2);
        $month_3 = $this->timezone->date($str_month_3);

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\Product\Model\Season $item
             */
            $item = $collection->getItemById($id);

            if (is_null($item)) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('No existe la temporada que intenta actualizar');
            }
        } else {
            $collection->addFieldToFilter('season_code', $code);
            $collection->addFieldToFilter('season_type_code', $type);
            $collection->addFieldToFilter('season_country_code', $country_code);

            if ($collection->getSize() > 0) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('Ya existe otra temporada con el mismo tipo, codigo y país');
            }
        }

        if (!$result_data['error']) {
            if ($start_at->getTimestamp() > $end_at->getTimestamp()) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('La fecha de inicio debe ser menor que la fecha de fin');
            }

            if (
                $month_1->getTimestamp() == $month_2->getTimestamp() ||
                $month_1->getTimestamp() == $month_3->getTimestamp() ||
                $month_2->getTimestamp() == $month_3->getTimestamp()
            ) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('Una de las fechas de entrega está duplicada');
            }
        }

        if (!$result_data['error']) {
            $config = $this->configStoreRepository->findStoreByFunctionCodeAndCountry(ConfigStoreInterface::FUNCTION_CODE_FUTURES, $country_code);
            if (is_null($config)) {
                $result_data['error'] = true;
                $result_data['messages'][] = __('No hay una tienda de futuros configurado el país que seleccionó');
            }
        }

        $result->setData($result_data);
        return $result;
    }

    public function _isAllowed()
    {
        return true;
    }
}
