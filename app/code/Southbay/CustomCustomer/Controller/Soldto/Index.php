<?php
namespace Southbay\CustomCustomer\Controller\Soldto;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\CustomerData\SectionPoolInterface;

class Index extends Action
{
    protected $resultPageFactory;
    protected $customerSession;
    /**
     * @var SectionPoolInterface
     */
    private $sectionPool;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        SectionPoolInterface $sectionPool,
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->sectionPool = $sectionPool;
        parent::__construct($context);
    }


public function execute()
{
    if ($this->getRequest()->isPost()) {

        $soldToId = $this->getRequest()->getParam('soldto');
        $this->customerSession->setSoldToId($soldToId);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('/');
        $this->sectionPool->getSectionsData(null, true);
        return $resultRedirect;
    }

    $resultPage = $this->resultPageFactory->create();
    return $resultPage;
}

}
