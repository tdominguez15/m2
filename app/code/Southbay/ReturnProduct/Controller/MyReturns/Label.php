<?php

namespace Southbay\ReturnProduct\Controller\MyReturns;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Io\File as IoFile;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Label implements HttpPostActionInterface
{
    private $fileFactory;
    private $directoryList;
    private $log;
    private $ioFile;
    private $context;
    private $return_product_repository;
    private $helper;

    public function __construct(Context                                                                     $context,
                                FileFactory                                                                 $fileFactory,
                                DirectoryList                                                               $directoryList,
                                IoFile                                                                      $ioFile,
                                \Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository $return_product_repository,
                                \Southbay\ReturnProduct\Helper\Data                                         $helper,
                                \Psr\Log\LoggerInterface                                                    $log)
    {
        $this->context = $context;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        $this->log = $log;
        $this->return_product_repository = $return_product_repository;
        $this->helper = $helper;
    }

    public function execute()
    {
        $return_id = $this->context->getRequest()->getParam('return_id');
        $packages = intval($this->context->getRequest()->getParam('packages'));

        try {
            $this->log->debug('Start label generation for return products', ['return_id' => $return_id, 'packages' => $packages]);

            $return_product = $this->return_product_repository->findById($return_id);

            if (is_null($return_product)) {
                throw new \Exception('Return product ' . $return_id . ' not found');
            }

            $base_path = 'export/labels';
            $directoryPath = $this->directoryList->getPath('var') . '/' . $base_path;

            if (!$this->ioFile->fileExists($directoryPath, false)) {
                $this->ioFile->mkdir($directoryPath, 0775);
            }

            $config = $this->helper->getConfig($return_product->getType(), $return_product->getCountryCode());

            $filename = 'southbay-label-return-product-id-' . $return_id . '.pdf';
            $filepath = $directoryPath . '/' . $filename;

            $label_text = '';

            if (!is_null($config)) {
                $label_text = $config->getLabelText();
            }

            $this->generatePDFLabel($filepath, $return_id, $return_product->getType(), $packages, $return_product->getCustomerName(), $label_text);

            $return_product->setLabelTotalPackages($packages);
            $return_product->setPrinted(true);
            $return_product->setPrintedAt(date('Y-m-d H:i:s'));

            $this->return_product_repository->save($return_product);

            return $this->fileFactory->create(
                $filename,
                [
                    'type' => 'filename',
                    'value' => $filepath,
                    'log' => $this->log,
                    'rm' => true
                ],
                'var');
        } finally {
            $this->log->debug('End label generation for return products', ['return_id' => $return_id, 'packages' => $packages]);
        }
    }

    private function generatePDFLabel($filepath, $return_id, $type, $packages, $customer_name, $dc_address)
    {
        $orientation = 'portrait';
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->_setPageSize([81, 81], $orientation);
        $mpdf->SetMargins(2, 2, 5);

        $qrCode = $this->generateQRCode($return_id);

        for ($i = 0; $i < $packages; $i++) {
            if ($i > 0) {
                $mpdf->AddPage();
            }
            $html = '<h6 style="margin: 2px; padding: 2px; text-align: center">' . __('Southbay - Solicitud de devolución') . ' #' . $return_id . '</h6>
                <p style="padding: 2px; margin: 2px; font-size: 9px; text-align: center"><span style="font-size: 8px">*</span>'. __($this->return_product_repository->getTypeName($type)) .'<span style="font-size: 8px">*</span></p>
                 <div style="text-align: center">' . $qrCode . '</div>
                 <p style="margin: 2px; padding: 2px; text-align: center; font-size: 10px; font-weight: bold">
                    ' . __('Paquetes: ') . ($i + 1) . '/' . $packages . '
                 </p>
                 <hr style="margin: 0; margin-bottom: 4px">
                 <p style="margin: 2px; padding: 2px; text-align: center; font-size: 9px">
                    ' . __('Cliente: ') . $customer_name . '
                 </p>
                 <p style="margin: 2px; padding: 2px; text-align: justify-all; font-size: 9px; max-height: 3em; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                    ' . __('Entregar en: ') . $dc_address . '
                 </p>
                 ';

            $mpdf->WriteHTML($html);
        }

        $mpdf->OutputFile($filepath);
    }

    private function generateQRCode($data)
    {
        $renderer = new ImageRenderer(
            new RendererStyle(83),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $str = $writer->writeString($data);
        return str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $str);
    }
}
