<?php

namespace Southbay\Product\Controller\Adminhtml\Season;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Southbay\CustomCustomer\Api\Data\ConfigStoreInterface;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $repository;

    private $collectionFactory;

    private $factory;

    private $timezone;

    private $configStoreRepository;

    private $resource;

    private $categoryCollectionFactory;

    private $categoryRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context                                                         $context,
        PageFactory                                                     $resultPageFactory,
        \Southbay\Product\Model\ResourceModel\Season\CollectionFactory  $collectionFactory,
        \Southbay\Product\Model\ResourceModel\Season                    $repository,
        \Southbay\CustomCustomer\Model\ConfigStoreRepository            $configStoreRepository,
        \Southbay\Product\Model\SeasonFactory                           $factory,
        \Magento\Framework\App\ResourceConnection                       $resource,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category                   $categoryRepository,
        TimezoneInterface                                               $timezone
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
        $this->timezone = $timezone;
        $this->configStoreRepository = $configStoreRepository;
        $this->resource = $resource;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $params = $this->getRequest()->getParams();

        $id = $params['fields']['season_id'] ?? null;
        $code = $params['fields']['season_code'];
        $type = $params['fields']['season_type_code'];
        $name = $params['fields']['season_name'];
        $description = $params['fields']['season_description'];
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

        $config = $this->configStoreRepository->findStoreByFunctionCodeAndCountry(ConfigStoreInterface::FUNCTION_CODE_FUTURES, $country_code);

        $collection = $this->collectionFactory->create();

        if ($id) {
            /**
             * @var \Southbay\Product\Model\Season $item
             */
            $item = $collection->getItemById($id);
        } else {
            /**
             * @var \Southbay\Product\Model\Season $item
             */
            $item = $this->factory->create();
            $item->setCountryCode($country_code);
            $item->setSeasonTypeCode($type);
            $item->setSeasonCode($code);
            $item->setStoreId($config->getSouthbayStoreCode());
        }

        $item->setSeasonName($name);
        $item->setSeasonDescription($description);

        $item->setStartAt($start_at->format('Y-m-d'));
        $item->setEndAt($end_at->format('Y-m-d'));

        $item->setMonthDeliveryDate1($month_1->format('Y-m-d'));
        $item->setMonthDeliveryDate2($month_2->format('Y-m-d'));
        $item->setMonthDeliveryDate3($month_3->format('Y-m-d'));

        $conn = $this->resource->getConnection();

        try {
            $conn->beginTransaction();
            if (!$id) {
                /**
                 * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
                 */
                $collection = $this->categoryCollectionFactory->create();
                $category_root_name = $item->getCountryCode() . '-' . $item->getSeasonCode() . ' (' . $config->getSouthbayFunctionCode() . ')';

                /**
                 * @var \Magento\Catalog\Model\Category $_category
                 */
                $_category = $collection->addAttributeToFilter('name', $category_root_name)->getFirstItem();

                if (is_null($_category->getId())) {
                    $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
                    /**
                     * @var \Magento\Catalog\Model\Category $parentCategory
                     */
                    $parentCategory = $this->categoryCollectionFactory->create()->getItemById($parentId);

                    $_category->setPath($parentCategory->getPath())
                        ->setUrlKey($category_root_name)
                        ->setParentId($parentId)
                        ->setName($category_root_name)
                        ->setIsActive(true);

                    $this->categoryRepository->save($_category);
                }
                $item->setSeasonCategoryId($_category->getId());
            }

            $this->repository->save($item);
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw $e;
        }

        $this->messageManager->addSuccessMessage(__('Datos guardados exitosamente.'));

        return $resultRedirect->setPath('*/*/');
    }

    public function _isAllowed()
    {
        return true;
    }
}

