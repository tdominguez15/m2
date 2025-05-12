<?php

namespace Southbay\Product\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order\StatusFactory;

class SapOrderStatusPatch implements DataPatchInterface
{
    const STATUS_SEND_TO_SAP = 'southbay_send_to_sap';
    const STATUS_SEND_ERROR = 'southbay_sap_error';
    const STATUS_CONFIRM_FAIL = 'southbay_sap_confirm_fail';
    const STATUS_CONFIRM = 'southbay_sap_confirm';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StatusFactory
     */
    private $statusFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory            $statusFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
    }

    /**
     * Apply Data Patch
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $status = $this->statusFactory->create();
        $status->setData('status', self::STATUS_SEND_TO_SAP)
            ->setData('label', 'Enviado a SAP')
            ->save();
        $status->assignState('complete', false, true);

        $status = $this->statusFactory->create();
        $status->setData('status', self::STATUS_SEND_ERROR)
            ->setData('label', 'Error')
            ->save();
        $status->assignState('complete', false, true);

        $status = $this->statusFactory->create();
        $status->setData('status', self::STATUS_CONFIRM_FAIL)
            ->setData('label', 'Error en confirmación de SAP')
            ->save();
        $status->assignState('complete', false, true);

        $status = $this->statusFactory->create();
        $status->setData('status', self::STATUS_CONFIRM)
            ->setData('label', 'Orden creada en SAP')
            ->save();
        $status->assignState('complete', false, true);

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
