<?php

namespace Southbay\Product\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\Order\Status;

class AddEnviadoOrderStatus implements DataPatchInterface
{
    /**
     * Status code for "Enviado"
     */
    const STATUS_ENVIADO = 'enviado';

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
        StatusFactory $statusFactory
    ) {
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
        $status->setData('status', self::STATUS_ENVIADO)
            ->setData('label', 'Enviado')
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

    /**
     * Get the status code for "Enviado"
     *
     * @return string
     */
    public static function getStatusEnviado()
    {
        return self::STATUS_ENVIADO;
    }
}
