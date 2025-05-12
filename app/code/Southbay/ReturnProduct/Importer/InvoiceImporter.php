<?php

namespace Southbay\ReturnProduct\Importer;

use Magento\Framework\Filesystem;
use Southbay\ReturnProduct\Model\Queue\InvoiceQueueMessage;

class InvoiceImporter
{
    private $log;
    private $filesystem;
    private $publisher;
    private $json;

    public function __construct(\Psr\Log\LoggerInterface                           $log,
                                Filesystem                                         $filesystem,
                                \Magento\Framework\Serialize\Serializer\Json       $json,
                                \Magento\Framework\MessageQueue\PublisherInterface $publisher)
    {
        $this->log = $log;
        $this->filesystem = $filesystem;
        $this->publisher = $publisher;
        $this->json = $json;
    }

    public function import($filename)
    {
        $this->log->info('Init invoice import');
        $path = $this->getFullPath($filename);
        $rows = $this->read($path);

        $this->log->info('rows', ['r' => count($rows)]);

        $this->write($rows);

        $this->log->info('End invoice import');
    }

    private function getFullPath($filename)
    {
        $media_folder = 'invoice/import';
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $target = $mediaDirectory->getAbsolutePath($media_folder);

        return $target . '/' . $filename;
    }

    private function read($path)
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $result = [];
        $result_map = [];

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $row_number = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $row_number++;
            $cell_number = 0;
            $stop = false;

            if ($row_number == 1) {
                continue;
            }

            $header = [
                'customer_code' => null,
                'customer_name' => null,
                'ship_to_code' => null,
                'ship_to_name' => null,
                'div_code' => null,
                'invoice_date' => null,
                'internal_invoice_number' => null,
                'invoice_ref' => null,
                'items' => []
            ];

            $item = [
                'sku' => null,
                'sku2' => null,
                'name' => null,
                'size' => null,
                'qty' => null,
                'amount' => null,
                'unit_price' => null,
                'net_unit_price' => null,
                'net_amount' => null
            ];

            foreach ($row->getCellIterator() as $cell) {
                $cell_number++;
                $value = $cell->getValue();

                if (empty($value)) {
                    $stop = true;
                    break;
                }

                switch ($cell_number) {
                    case 1:
                    {
                        $header['customer_code'] = trim($value);
                        break;
                    }
                    case 2:
                    {
                        $header['customer_name'] = trim($value);
                        break;
                    }
                    case 3:
                    {
                        $header['ship_to_code'] = trim($value);
                        break;
                    }
                    case 4:
                    {
                        $header['ship_to_name'] = trim($value);
                        break;
                    }
                    case 5:
                    {
                        $header['div_code'] = trim($value);
                        break;
                    }
                    case 6:
                    {
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        $header['invoice_date'] = $date->format('y-m-d');
                        break;
                    }
                    case 7:
                    {
                        $value = trim($value);
                        $header['internal_invoice_number'] = $value;
                        break;
                    }
                    case 8:
                    {
                        $header['invoice_ref'] = trim($value);
                        break;
                    }
                    case 9:
                    {
                        $item['sku'] = trim($value);
                        break;
                    }
                    case 10:
                    {
                        $item['name'] = trim($value);
                        break;
                    }
                    case 11:
                    {
                        $item['size'] = trim($value);
                        $item['sku2'] = $item['sku'] . '/' . $item['size'];
                        break;
                    }
                    case 12:
                    {
                        $item['qty'] = intval(trim($value));
                        break;
                    }
                    case 13:
                    {
                        $item['amount'] = floatval(trim($value));
                        break;
                    }
                    case 14:
                    {
                        $item['unit_price'] = floatval(trim($value));
                        break;
                    }
                    case 15:
                    {
                        $item['net_amount'] = floatval(trim($value));
                        $item['net_unit_price'] = $item['net_amount'] / $item['qty'];
                        break;
                    }
                }

                if ($cell_number == 8) {
                    $internal_invoice_number = $header['internal_invoice_number'];

                    if (!isset($result_map[$internal_invoice_number])) {
                        $result_map[$internal_invoice_number] = $header;
                        $result[] = &$result_map[$internal_invoice_number];
                    }
                } else if ($cell_number == 15) {
                    $internal_invoice_number = $header['internal_invoice_number'];
                    $result_map[$internal_invoice_number]['items'][] = $item;
                }
            }

            if ($stop) {
                break;
            }
        }


        return $result;
    }

    private function write($rows)
    {
        foreach ($rows as $row) {
            $items = $row['items'];
            unset($row['items']);

            $this->publisher->publish(
                'southbay_return_product_invoice_import',
                $this->json->serialize([
                    'invoice' => $row,
                    'items' => $items
                ])
            );
        }

        /*
        $count = 0;
        foreach ($rows as $row) {
            $count++;

            if ($count < 5) {
                $this->publisher->publish(
                    'southbay_return_invoice_import',
                    $this->json->serialize($row)
                );
            } else {
                break;
            }
        }
        */
    }
}
