<?php

namespace Southbay\Product\Controller\Adminhtml\Season;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Config\Model\ResourceModel\Config as ConfigResourceConfig;
use Magento\Store\Model\StoreManagerInterface;

class Active extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;

    private $quoteCollectionFactory;

    private $cache_manager;

    private $configResource;

    private $storeManager;

    private $log;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                        $context,
        PageFactory                                                    $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\Season\CollectionFactory $collectionFactory,
        \Southbay\Product\Model\ResourceModel\Season                   $repository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory     $quoteCollectionFactory,
        \Magento\Framework\App\Cache\Manager                           $cache_manager,
        StoreManagerInterface                                          $storeManager,
        ConfigResourceConfig                                           $configResource,
        \Psr\Log\LoggerInterface                                       $log
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cache_manager = $cache_manager;
        $this->configResource = $configResource;
        $this->storeManager = $storeManager;
        $this->log = $log;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $id = $params['id'];

        if (!$id) {
            $this->messageManager->addErrorMessage(__('No se puede buscar el registro que intenta eliminar'));
        } else {
            /**
             * @var \Southbay\Product\Model\Season $item
             */
            $item = $this->collectionFactory->create()->getItemById($id);

            if ($item) {
                $items = $this->collectionFactory->create()
                    ->addFieldToFilter('season_id', ['neq' => $id])
                    ->addFieldToFilter('season_country_code', $item->getCountryCode())
                    ->getItems();

                /**
                 * @var \Southbay\Product\Model\Season $other_item
                 */
                foreach ($items as $other_item) {
                    $other_item->setActive(false);
                    $this->repository->save($other_item);
                }

                $item->setActive(true);
                $this->repository->save($item);

                /**
                 * @var \Magento\Store\Model\Store $store
                 */
                $store = $this->storeManager->getStore($item->getStoreId());
                $group = $store->getGroup();

                if ($group->getRootCategoryId() != $item->getSeasonCategoryId()) {
                    $group->setRootCategoryId($item->getSeasonCategoryId());
                    $group->save();

                    $this->cache_manager->flush(['config', 'full_page', 'config_webservice']);
                }

                $this->messageManager->addSuccessMessage(__('Temporada activada'));
            } else {
                $this->messageManager->addErrorMessage(__('No existe el registro que intenta eliminar'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}
