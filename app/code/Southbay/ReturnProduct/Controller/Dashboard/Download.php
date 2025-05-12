<?php

namespace Southbay\ReturnProduct\Controller\Dashboard;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Magento\Framework\Filesystem\Io\File as IoFile;
use \Psr\Log\LoggerInterface;
use Southbay\ReturnProduct\Controller\Adminhtml\Dashboard\Download as AdminADashboard;
use Magento\Framework\Api\Filter;
use Southbay\ReturnProduct\Model\ResourceModel\SouthbayReturnProductRepository;
use Magento\Customer\Model\Session as CustomerSession;

class Download extends Action
{
    protected $resultFactory;
    protected $fileFactory;
    protected $directoryList;
    protected $ioFile;
    protected $adminADashboard;
    protected $log;
    protected $repository;
    protected $customerSession;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        FileFactory $fileFactory,
        DirectoryList $directoryList,
        IoFile $ioFile,
        AdminADashboard $adminADashboard,
        LoggerInterface  $log,
        CustomerSession $customerSession,
        SouthbayReturnProductRepository $repository
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        $this->adminADashboard = $adminADashboard;
        $this->log = $log;
        $this->customerSession = $customerSession;
        $this->repository = $repository;
    }

    public function execute()
    {
        $customerEmail = $this->customerSession->getCustomer()->getEmail();

        if (!$customerEmail) {
            $this->log->error('No se pudo obtener el correo electrónico del cliente logueado.');
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                ->setData(['success' => false, 'message' => 'Sesion expiró']);
        }
        $collection = $this->repository->getAll($this->customerSession->getCustomer()->getEmail());

        $filters = $this->getRequest()->getParam('filter');
        $filterObjects = $this->formatFiltersToApiFilter($filters);

        $base_path = 'export';
        $directoryPath = $this->directoryList->getPath('var') . '/' . $base_path;

        if (!$this->ioFile->fileExists($directoryPath, false)) {
            $this->ioFile->mkdir($directoryPath, 0775);
        }

        $collection = $this->adminADashboard->loadFilteredCollection($collection,$filterObjects);
        $spreadsheet =  $this->adminADashboard->loadSheet($collection,true);



        $filename = 'rtv-frontend' . date('Y-m-d-s') . '.xlsx';
        $filepath = $directoryPath . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $this->fileFactory->create($filename, ['type' => 'filename', 'value' => $filepath, 'log' => $this->log, 'rm' => true], 'var');
    }



    protected function formatFiltersToApiFilter($filters)
    {
        $filterObjects = [];

        if (!empty($filters)) {
            if (!is_array($filters)) {
                $filters = json_decode($filters, true);
            }
            if (is_array($filters)) {
                foreach ($filters as $field => $data) {

                    if (strpos($field, 'From') !== false || strpos($field, 'To') !== false) {
                        $baseField = preg_replace('/(_From|_To)$/', '', $field);
                        $value = $data['value'];
                        if (strpos($field, 'From') !== false) {
                            $filterObjects[] = new Filter([
                                'field' => $baseField,
                                'value' => $value,
                                'condition_type' => 'gteq'
                            ]);
                        } elseif (strpos($field, 'To') !== false) {
                            $filterObjects[] = new Filter([
                                'field' => $baseField,
                                'value' => $value,
                                'condition_type' => 'lteq'
                            ]);
                        }
                    } else {

                        if (!empty($data['value'])) {
                            $value = $data['value'];
                            $condition = $data['condition'] ?? 'eq';
                            $filterObjects[] = new Filter([
                                'field' => $field,
                                'value' => $value,
                                'condition_type' => $condition
                            ]);
                        }
                    }
                }
            }
        }

        return $filterObjects;
    }
}
