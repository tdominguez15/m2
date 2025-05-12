<?php

namespace Southbay\ReturnProduct\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\View\Result\PageFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnControlQaRepository;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnFinancialApprovalRepository;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnReceptionRepository;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbaySapInterfaceRepository;
use Southbay\ReturnProduct\Helper\SendSapRtvRequest;
use Southbay\CustomCustomer\Model\ResourceModel\SoldTo\CollectionFactory as SoldToCollectionFactory;

class Download extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $fileFactory;
    protected $directoryList;
    protected $ioFile;
    protected $log;

    private $returnProductRepository;
    private $returnProductItemRepository;
    private $invoiceRepository;
    private $invoiceItemRepository;

    private $control_qa_item_collection_factory;
    private $southbay_helper;

    private $sessionManager;

    private $statusOptionsProvider;

    private $countryOptionsProvider;

    private $receptionRepository;

    private $sapInterfaceRepository;
    private $controlQaRepository;
    private $financialApprovalRepository;
    private $sendSapRtvRequest;

    /**
     * @var SoldToCollectionFactory
     */
    private $soldToCollectionFactory;


    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     *
     */
    public function __construct(
        Context                                                                                             $context,
        \Psr\Log\LoggerInterface                                                                            $log,
        FileFactory                                                                                         $fileFactory,
        DirectoryList                                                                                       $directoryList,
        PageFactory                                                                                         $resultPageFactory,
        IoFile                                                                                              $ioFile,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository                         $returnProductRepository,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductItemRepository                     $returnProductItemRepository,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceRepository                               $invoiceRepository,
        \Southbay\ReturnProduct\Model\ResourceModel\SouthbayInvoiceItemRepository                           $invoiceItemRepository,
        \Southbay\ReturnProduct\Helper\Data                                                                 $southbay_helper,
        \Southbay\ReturnProduct\Model\ResourceModel\Collection\SouthbayReturnControlQaItemCollectionFactory $control_qa_item_collection_factory,
        \Southbay\ReturnProduct\Block\Adminhtml\Config\Form\StatusOptionsProvider                           $statusOptionsProvider,
        \Southbay\ReturnProduct\Block\Adminhtml\Config\Form\CountryOptionsProvider                          $countryOptionsProvider,
        \Magento\Framework\Session\SessionManager                                                           $sessionManager,
        SouthbayReturnReceptionRepository                                                                   $receptionRepository,
        SouthbaySapInterfaceRepository                                                                      $sapInterfaceRepository,
        SouthbayReturnControlQaRepository                                                                   $controlQaRepository,
        SouthbayReturnFinancialApprovalRepository                                                           $financialApprovalRepository,
        SendSapRtvRequest                                                                                   $sendSapRtvRequest,
        SoldToCollectionFactory                                                                             $soldToCollectionFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        $this->log = $log;
        $this->returnProductRepository = $returnProductRepository;
        $this->returnProductItemRepository = $returnProductItemRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceItemRepository = $invoiceItemRepository;
        $this->control_qa_item_collection_factory = $control_qa_item_collection_factory;
        $this->southbay_helper = $southbay_helper;
        $this->statusOptionsProvider = $statusOptionsProvider;
        $this->sessionManager = $sessionManager;
        $this->countryOptionsProvider = $countryOptionsProvider;
        $this->receptionRepository = $receptionRepository;
        $this->controlQaRepository = $controlQaRepository;
        $this->sapInterfaceRepository = $sapInterfaceRepository;
        $this->financialApprovalRepository = $financialApprovalRepository;
        $this->sendSapRtvRequest = $sendSapRtvRequest;
        $this->soldToCollectionFactory = $soldToCollectionFactory;
    }

    public function execute()
    {
        $base_path = 'export';

        $directoryPath = $this->directoryList->getPath('var') . '/' . $base_path;

        if (!$this->ioFile->fileExists($directoryPath, false)) {
            $this->ioFile->mkdir($directoryPath, 0775);
        }



        $config_checker = $this->southbay_helper->getTypeReturnByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_CHECK);
        $show_all = !empty($config_checker);

        if (!$show_all) {
            $config_approval = $this->southbay_helper->getTypeReturnByTypeRol(\Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct::TYPE_ROL_CODE_APPROVAL);
            $show_all = !empty($config_approval);
        }

        $filters = $this->sessionManager->getDashboardFilters();
        $collection = $this->returnProductRepository->getDashboardCollection();
        $collection = $this->loadFilteredCollection($collection, $filters);
        $spreadsheet = $this->loadSheet($collection, $show_all);

        $filename = 'rtv-' . date('Y-m-d') . '.xlsx';
        $filepath = $directoryPath . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $this->fileFactory->create($filename, ['type' => 'filename', 'value' => $filepath, 'log' => $this->log, 'rm' => true], 'var');
    }


    public function loadSheet($collection, $show_all)
    {
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $sold_names_cache = [];
        $ship_to_cache = [];

        $list = $collection;
        $map_reason_reject = $this->southbay_helper->getReasonReject(true);

        $countryOptions = $this->countryOptionsProvider->toOptionArray();
        $countries_map = [];

        foreach ($countryOptions as $option) {
            $countries_map[$option['value']] = $option['label'];
        }

        $statusOptions = $this->statusOptionsProvider->toOptionArray();
        $status_map = [];

        foreach ($statusOptions as $option) {
            $status_map[$option['value']] = $option['label'];
        }

        if ($show_all) {
            $rows = [
                [
                    __('Pais'),
                    __('Cliente'),
                    __('NºDevolución'),
                    __('Tipo'),
                    __('Estado'),
                    __('Fecha ultima actualización'),
                    __('Nº Factura'),
                    __('Solicitante'),
                    __('Codigo'),
                    __('Puerta'),
                    __('Codigo'),
                    __('Referencia'),
                    __('Material'),
                    __('Sku'),
                    __('Talle'),
                    __('Variante'),
                    __('Unidad de negocio'),
                    __('Precio unitario'),
                    __('Precio'),
                    __('Observaciones del cliente'),
                    __('Cant Solicitada'),
                    __('Cant Total Devuelta'),
                    __('Cant Faltante'),
                    __('Cant Sobrante'),
                    __('Cant Rechazada'),
                    __('Cant Aceptada'),
                    __('Bultos informados por el cliente'),
                    __('Bultos recepcionados'),
                    __('Control de calidad - Motivo devolución'),
                    __('Control de calidad - Observaciones'),
                    __('Fecha creación'),
                    __('Fecha recepción'),
                    __('Fecha control'),
                    __('Fecha aprobación'),
                    __('Fecha confirmación'),
                    __('Total NC'),
                    __('Total NC Enviados')
                ]
            ];
        } else {
            $rows = [
                [
                    __('Pais'),
                    __('Cliente'),
                    __('NºDevolución'),
                    __('Tipo'),
                    __('Estado'),
                    __('Fecha ultima actualización'),
                    __('Solicitante'),
                    __('Codigo'),
                    __('Puerta'),
                    __('Codigo'),
                    __('Material'),
                    __('Sku'),
                    __('Talle'),
                    __('Variante'),
                    __('Unidad de negocio'),
                    __('Observaciones del cliente'),
                    __('Cant Solicitada'),
                    __('Cant Total Devuelta'),
                    __('Cant Faltante'),
                    __('Cant Sobrante'),
                    __('Cant Rechazada'),
                    __('Cant Aceptada'),
                    __('Bultos informados por el cliente'),
                    __('Bultos recepcionados'),
                    __('Control de calidad - Motivo devolución'),
                    __('Control de calidad - Observaciones'),
                    __('Fecha creación'),
                    __('Fecha recepción'),
                    __('Fecha control'),
                    __('Fecha aprobación'),
                    __('Fecha confirmación')
                ]
            ];
        }

        $map_invoices = [];

        /**
         * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct $return_product
         */
        foreach ($list as $return_product) {
            $items = $this->returnProductItemRepository->findByReturnId($return_product->getId());

            if (count($items) == 0) {
                continue;
            }

            $control_qa_map = [];
            $reception_date = null;
            $control_qa_date = null;
            $approval_date = null;
            $confirmation_date = null;
            $reception_packages = 0;
            $total_documents = 0;
            $total_success = 0;

           $control_qa = $this->controlQaRepository->findByReturnProductId($return_product->getId());

            if (!is_null($control_qa)) {
                $control_qa_date = $control_qa->getCreatedAt();
                $control_qa_date = date('m-d-y', strtotime($control_qa_date));
            }

            $approval = $this->financialApprovalRepository->findByReturnProductId($return_product->getId());

            if (!is_null($approval)) {
                $approval_date = $approval->getCreatedAt();
                $approval_date = date('m-d-y', strtotime($approval_date));
            }

            $sapDocInfo = $this->sapInterfaceRepository->findLastByReturnProductId($return_product->getId());

            if (!is_null($sapDocInfo)) {
                $confirmation_date = $sapDocInfo['first']->getCreatedAt();
                $confirmation_date = date('m-d-y', strtotime($confirmation_date));
                $total_documents = $sapDocInfo['total'];
                $total_success = $sapDocInfo['success'];
            }

            /**
             * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
             */
            $control_qa_item_collection = $this->control_qa_item_collection_factory->create();
            $control_qa_items = $control_qa_item_collection->addFieldToFilter(
                \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem::ENTITY_RETURN_ID, ['eq' => $return_product->getId()]
            );

            $control_qa_items->load();

            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem $item_control_qa
             */
            foreach ($control_qa_items->getItems() as $item_control_qa) {
                $key = $item_control_qa->getSku() . '.' . $item_control_qa->getSize();
                $control_qa_map[$key] = $item_control_qa;
            }

            $reception = $this->receptionRepository->findByReturnProductId($return_product->getId());

            if (!is_null($reception)) {
                $reception_packages = $reception->getTotalPackages();
                $reception_date = $reception->getCreatedAt();
                $reception_date = date('m-d-y', strtotime($reception_date));
            }


            /**
             * @var \Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem $item
             */
            foreach ($items as $item) {
                if (!isset($map_invoices[$item->getInvoiceid()])) {
                    $map_invoices[$item->getInvoiceid()] = $this->invoiceRepository->findById($item->getInvoiceid());
                }
                $invoice = $map_invoices[$item->getInvoiceid()];

                if (is_null($invoice)) {
                    $this->log->error('Invoice not found', ['return_product_id' => $item->getReturnId(), 'invoice_id' => $item->getInvoiceid()]);
                    break;
                }

                $invoice_item = $this->invoiceItemRepository->findById($item->getInvoiceItemId());

                if (is_null($invoice_item)) {
                    $this->log->error('Invoice item not found', ['return_product_id' => $item->getReturnId(), 'invoice_id' => $item->getInvoiceid(), 'invoice_item_id' => $item->getInvoiceid()]);
                    break;
                }

                $updated_at = date('m-d-y', strtotime($return_product->getUpdatedAt()));
                $create_date = date('m-d-y', strtotime($return_product->getCreatedAt()));

                $customerCode = $return_product->getCustomerCode();
                $invoiceSoldToCode = $invoice->getCustomerCode();
                $invoiceShipToCode = $invoice->getCustomerShipToCode();

                $_ship_to_cache_key = $customerCode . '-' . $invoiceShipToCode . '-' . $invoiceSoldToCode;
                if(isset($ship_to_cache[$_ship_to_cache_key])) {
                    $shipTo = $ship_to_cache[$_ship_to_cache_key];
                } else {
                    $shipTo = $this->sendSapRtvRequest->findShipTo($customerCode,$invoiceShipToCode,$invoiceSoldToCode);
                    $ship_to_cache[$_ship_to_cache_key] = $shipTo;
                }

                $shipToName = $shipTo && $shipTo->getName() ? $shipTo->getName() : '';
                $shipToCode = $shipTo && $shipTo->getCode() ? $shipTo->getCode() : '';

                $_sold_to_cache_key = $shipTo ? $shipTo->getCustomerCode() : '';
                if(isset($sold_names_cache[$_sold_to_cache_key])) {
                    $soldTo = $sold_names_cache[$_sold_to_cache_key];
                } else {
                    $soldToCollection = $this->soldToCollectionFactory->create();
                    $soldTo = $soldToCollection->addFieldToFilter('southbay_sold_to_customer_code', $_sold_to_cache_key)->getFirstItem();
                    $sold_names_cache[$_sold_to_cache_key] = $soldTo;
                }

                $soldToName = $soldTo && $soldTo->getSouthbaySoldToCustomerName() ? $soldTo->getSouthbaySoldToCustomerName() : '';

                if ($show_all) {
                    $_row = [
                        $countries_map[$return_product->getCountryCode()],
                        // $return_product->getCustomerName(),
                        $soldToName,
                        $return_product->getId(),
                        $this->returnProductRepository->getTypeName($return_product->getType()),
                        $status_map[$return_product->getStatus()],
                        $updated_at,
                        $invoice->getIntInvoiceNum(),

                        $soldToName,
                        $invoiceSoldToCode,
                        $shipToName,
                        $shipToCode,

                        $invoice->getInvoiceRef(),
                        $item->getName(),
                        $item->getSku(),
                        $item->getSize(),
                        $invoice_item->getSkuVariant(),
                        $this->getBuName($invoice_item->getBu()),
                        $item->getNetUnitPrice(),
                        $item->getNetAmount(),
                        $item->getReasonsText(),
                        $item->getQty(),
                        $item->getQtyReal(),
                        $item->getQtyMissing(),
                        $item->getQtyExtra(),
                        $item->getQtyRejected(),
                        $item->getQtyAccepted(),
                        $return_product->getLabelTotalPackages(),
                        strval($reception_packages)
                    ];
                } else {
                    $_row = [
                        $countries_map[$return_product->getCountryCode()],
                        $soldToName, // $return_product->getCustomerName(),
                        $return_product->getId(),
                        $this->returnProductRepository->getTypeName($return_product->getType()),
                        $status_map[$return_product->getStatus()],
                        $updated_at,

                        $soldToName,
                        $invoiceSoldToCode,
                        $shipToName,
                        $shipToCode,

                        $item->getName(),
                        $item->getSku(),
                        $item->getSize(),
                        $invoice_item->getSkuVariant(),
                        $this->getBuName($invoice_item->getBu()),
                        $item->getReasonsText(),
                        $item->getQty(),
                        $item->getQtyReal(),
                        $item->getQtyMissing(),
                        $item->getQtyExtra(),
                        $item->getQtyRejected(),
                        $item->getQtyAccepted(),
                        $return_product->getLabelTotalPackages(),
                        strval($reception_packages)
                    ];
                }

                $reasons_rejected = [];
                $reasons_text = '';

                $key = $item->getSku() . '.' . $item->getSize();

                if (isset($control_qa_map[$key])) {
                    $control_qa_item = $control_qa_map[$key];
                    $reasons_text = $control_qa_item->getReasonText();
                    if (!empty($control_qa_item->getReasonCodes())) {
                        $codes = explode(',', $control_qa_item->getReasonCodes());
                        foreach ($codes as $code) {
                            if (isset($map_reason_reject[$code])) {
                                $reasons_rejected[] = $code . '-' . $map_reason_reject[$code];
                            }
                        }
                    }
                }

                $_row[] = implode(',', $reasons_rejected);
                $_row[] = $reasons_text;

                $_row[] = $create_date;
                $_row[] = $reception_date;
                $_row[] = $control_qa_date;
                $_row[] = $approval_date;
                $_row[] = $confirmation_date;

                if ($show_all) {
                    $_row[] = strval($total_documents);
                    $_row[] = strval($total_success);
                }

                $rows[] = $_row;
            }
        }

        $activeWorksheet->fromArray($rows);
        return $spreadsheet;
    }

    /**
     * @param \Southbay\CustomCustomer\Api\Data\ShipToInterface $ship_to
     * @return void
     */
    private function getShipTo($ship_to)
    {
        if (!empty($ship_to->getName())) {
            return $ship_to->getName();
        } else {
            return $ship_to->getAddress() . ' ' . $ship_to->getAddressNumber() . ' ' . $ship_to->getState();
        }
    }

    private function getBuName($bu)
    {
        $result = '';

        $bu = strtolower($bu);

        if ($bu == '10' || $bu == 'ropa') {
            $result = __('Ropa');
        } else if ($bu == '20' || $bu == 'calzado') {
            $result = __('Calzado');
        } else if ($bu == '30' || $bu == 'accesorios' || $bu == 'accesorio') {
            $result = __('Accesorios');
        }
        return $result;
    }

    public function _isAllowed()
    {
        return true;
    }


    public function loadFilteredCollection($collection, $filters = null)
    {
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
            }
        }
        $collection->load();

        return $collection->getItems();
    }


}
