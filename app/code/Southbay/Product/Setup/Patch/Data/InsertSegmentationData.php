<?php


namespace Southbay\Product\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Southbay\Product\Model\SegmentationFactory;

class InsertSegmentationData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SegmentationFactory
     */
    private $segmentationFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SegmentationFactory $segmentationFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SegmentationFactory      $segmentationFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->segmentationFactory = $segmentationFactory;
    }

    /**
     * Applies the data patch to insert initial segmentation data.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $data = [
            ['code' => 'NDDC', 'label' => 'NDDC - NIKE DIRECT DIGITAL COMMERCE'],
            ['code' => 'NSO', 'label' => 'NSO - NIKE STORE OWNED'],
            ['code' => 'NSP', 'label' => 'NSP - NIKE STORE PARTNER'],
            ['code' => 'SG', 'label' => 'SG - SPORTING GOODS'],
            ['code' => 'AS', 'label' => 'AS - ATHLETIC SPECIALTY'],
            ['code' => 'CS BKST', 'label' => 'CS BKST - CATEGORY SPECIALTY BASKET'],
            ['code' => 'CS RUN', 'label' => 'CS RUN - CATEGORY SPECIALTY RUNNING'],
            ['code' => 'CS NIKE SB', 'label' => 'CS NIKE SB - CATEGORY SPECIALTY NIKE SB'],
            ['code' => 'CS FTBL', 'label' => 'CS FTBL - CATEGORY SPECIALTY FUTBOL'],
            ['code' => 'NBHD', 'label' => 'NBHD - CATEGORY SPECIALTY NEIGHBORHOOD'],
            ['code' => 'MELI', 'label' => 'MELI - MERCADO LIBRE'],
            ['code' => 'NVS', 'label' => 'NVS - NIKE VALUE STORE'],
            ['code' => 'FRONTERA', 'label' => 'FRONTERA - FRONTERA'],
            ['code' => 'CS NBA', 'label' => 'CS NBA - CATEGORY SPECIALTY NBA'],
            ['code' => '*all-for-southbay*', 'label' => 'ALL PRODUCTS']
        ];

        foreach ($data as $itemData) {
            $segmentation = $this->segmentationFactory->create();
            $segmentation->setData($itemData)->save();
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
