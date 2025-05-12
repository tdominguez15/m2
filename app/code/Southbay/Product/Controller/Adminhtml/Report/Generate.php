<?php

namespace Southbay\Product\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;

class Generate extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $report;

    private $collectionFactory;

    private $fileFactory;

    private $filesystem;

    private $timezone;

    private $log;

    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                                              $context,
        PageFactory                                                                          $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\SouthbayProductImportHistory\CollectionFactory $collectionFactory,
        \Southbay\CustomCheckout\Model\Report\SouthbayFutureOrderEntry                       $report,
        JsonFactory                                                                          $resultJsonFactory,
        FileFactory                                                                          $fileFactory,
        Filesystem                                                                           $filesystem,
        TimezoneInterface                                                                    $timezone,
        \Psr\Log\LoggerInterface                                                             $log
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->report = $report;
        $this->timezone = $timezone;
        $this->log = $log;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();

        $type = $params['fields']['type'];
        $str_from = $params['fields']['from'];
        $str_to = $params['fields']['to'];
        $store_id = $params['fields']['store_id'];
        $sold_to_list = $params['fields']['sold_to_list'] ?? [];

        $from = $this->timezone->date($str_from);
        $to = $this->timezone->date($str_to);

        $data = [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'store_id' => $store_id,
            'sold_to_list' => $sold_to_list
        ];

        try {
            $filepath = $this->report->generate($data);
            $content = file_get_contents($filepath);
            unlink($filepath);

            $result = $this->resultJsonFactory->create();

            return $result->setData([
                'result' => $content,
                'error' => false
            ]);
        } catch (\Exception $e) {
            $this->log->error('Error generating report: ', ['e' => $e]);
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        if ($type == 'future') {
            return $resultRedirect->setPath('*/*/future');
        } else {
            return $resultRedirect->setPath('*/*/atonce');
        }
    }

    public function _isAllowed()
    {
        return true;
    }
}

