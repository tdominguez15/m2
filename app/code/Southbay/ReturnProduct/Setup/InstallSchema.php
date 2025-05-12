<?php

namespace Southbay\ReturnProduct\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoice as SouthbayInvoiceInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayInvoiceItem as SouthbayInvoiceItemInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnBalanceItem as SouthbayReturnBalanceItemInterface;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQa as SouthbayReturnControlQaInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnControlQaItem as SouthbayReturnControlQaItemInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnFinancialApproval as SouthbayReturnFinancialApprovalInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnProduct as SouthbayReturnProductInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnProductItem as SouthbayReturnProductItemInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnReception as SouthbayReturnReceptionInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReasonReject as SouthbayReasonRejectInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReasonReturn as SouthbayReasonReturnInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayReturnConfig as SouthbayReturnConfigInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayExchangeReturn as SouthbayExchangeReturnInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayRolConfigRtv as SouthbayRolConfigRtvInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtv as SouthbayConfigNotificationRtvInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayConfigNotificationRtvByRol as SouthbayConfigNotificationRtvByRolInterfase;
use Southbay\ReturnProduct\Api\Data\SouthbayNotificationHistoryRtv as SouthbayNotificationHistoryRtvInterfase;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createInvoiceTable($setup);
        $this->createInvoiceItemTable($setup);
        $this->createSouthbayReturnBalanceItemTable($setup);
        $this->createSouthbayReturnControlQaTable($setup);
        $this->createSouthbayReturnControlQaItemTable($setup);
        $this->createSouthbayReturnFinancialApprovalTable($setup);
        $this->createSouthbayReturnProductTable($setup);
        $this->createSouthbayReturnProductItemTable($setup);
        $this->createSouthbayReturnReceptionTable($setup);
        $this->createSouthbayReasonReturnTable($setup);
        $this->createSouthbayReasonRejectTable($setup);
        $this->createSouthbayConfigTable($setup);

        $this->createSouthbayExchangeReturnTable($setup);
        $this->createSouthbayRolConfigRtvTable($setup);
        $this->createSouthbayConfigNotificationRtvTable($setup);
        $this->createSouthbayConfigNotificationRtvByRolTable($setup);
        $this->createSouthbayNotificationHistoryRtvTable($setup);

        $setup->endSetup();
    }

    private function createInvoiceTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayInvoiceInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayInvoiceInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayInvoiceInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_OLD_INVOICE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    1,
                    ['nullable' => false, 'default' => 0]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_CUSTOMER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_CUSTOMER_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => true]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => true]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_INVOICE_DATE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_INTERNAL_INVOICE_NUMBER,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    15,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceInterfase::ENTITY_INVOICE_REF,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false]
                )
                ->addColumn(
                    SouthbayInvoiceInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayInvoiceInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Southbay Invoice');
            $setup->getConnection()->createTable($table);
        }

        $setup->getConnection()
            ->addIndex(
                $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                $setup->getIdxName(
                    $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                    [SouthbayInvoiceInterfase::ENTITY_INTERNAL_INVOICE_NUMBER, SouthbayInvoiceInterfase::ENTITY_INVOICE_DATE, SouthbayInvoiceInterfase::ENTITY_CUSTOMER_CODE, SouthbayInvoiceInterfase::ENTITY_COUNTRY_CODE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [SouthbayInvoiceInterfase::ENTITY_INTERNAL_INVOICE_NUMBER, SouthbayInvoiceInterfase::ENTITY_INVOICE_DATE, SouthbayInvoiceInterfase::ENTITY_CUSTOMER_CODE, SouthbayInvoiceInterfase::ENTITY_COUNTRY_CODE],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                $setup->getIdxName(
                    $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                    [SouthbayInvoiceInterfase::ENTITY_COUNTRY_CODE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [SouthbayInvoiceInterfase::ENTITY_COUNTRY_CODE],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                $setup->getIdxName(
                    $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                    [SouthbayInvoiceInterfase::ENTITY_INVOICE_REF],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [SouthbayInvoiceInterfase::ENTITY_INVOICE_REF],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                $setup->getIdxName(
                    $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                    [SouthbayInvoiceInterfase::ENTITY_INVOICE_DATE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [SouthbayInvoiceInterfase::ENTITY_INVOICE_DATE],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                $setup->getIdxName(
                    $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                    [SouthbayInvoiceInterfase::ENTITY_CUSTOMER_CODE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [SouthbayInvoiceInterfase::ENTITY_CUSTOMER_CODE],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                $setup->getIdxName(
                    $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                    [SouthbayInvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_CODE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [SouthbayInvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_CODE]
            );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                $setup->getIdxName(
                    $setup->getTable(SouthbayInvoiceInterfase::TABLE),
                    [SouthbayInvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_CODE, SouthbayInvoiceInterfase::ENTITY_CUSTOMER_CODE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [SouthbayInvoiceInterfase::ENTITY_CUSTOMER_SHIP_TO_CODE, SouthbayInvoiceInterfase::ENTITY_CUSTOMER_CODE]
            );
    }

    private function createInvoiceItemTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayInvoiceItemInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayInvoiceItemInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayInvoiceItemInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    SouthbayInvoiceItemInterfase::ENTITY_INVOICE_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_SKU,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_SKU2,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    120,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_SKU_GENERIC,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    ['nullable' => true]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_SKU_VARIANT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    ['nullable' => true]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_POSITION,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    30,
                    ['nullable' => true]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_BU,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_SIZE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    30,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_QTY,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_AMOUNT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => false, 'unsigned' => true, 'precision' => 10, 'scale' => 3]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_UNIT_PRICE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => false, 'unsigned' => true, 'precision' => 10, 'scale' => 3]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_NET_UNIT_PRICE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => true, 'unsigned' => true, 'precision' => 10, 'scale' => 3]
                )
                ->addColumn(SouthbayInvoiceItemInterfase::ENTITY_NET_AMOUNT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => false, 'unsigned' => true, 'precision' => 10, 'scale' => 3]
                )
                ->addColumn(
                    SouthbayInvoiceItemInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayInvoiceItemInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                        [SouthbayInvoiceItemInterfase::ENTITY_SKU],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayInvoiceItemInterfase::ENTITY_SKU],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                        [SouthbayInvoiceItemInterfase::ENTITY_SKU_GENERIC],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayInvoiceItemInterfase::ENTITY_SKU_GENERIC],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                        [SouthbayInvoiceItemInterfase::ENTITY_SKU_VARIANT],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayInvoiceItemInterfase::ENTITY_SKU_VARIANT],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayInvoiceItemInterfase::TABLE),
                        [SouthbayInvoiceItemInterfase::ENTITY_INVOICE_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayInvoiceItemInterfase::ENTITY_INVOICE_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }

    private function createSouthbayReturnBalanceItemTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnBalanceItemInterface::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnBalanceItemInterface::TABLE)
                )
                ->addColumn(
                    SouthbayReturnBalanceItemInterface::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ]
                )
                ->addColumn(
                    SouthbayReturnBalanceItemInterface::ENTITY_INVOICE_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => false,
                        'nullable' => false,
                        'primary' => false,
                        'unsigned' => true,
                    ]
                )
                ->addColumn(
                    SouthbayReturnBalanceItemInterface::ENTITY_INVOICE_ITEM_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => false,
                        'nullable' => false,
                        'primary' => false,
                        'unsigned' => true,
                    ]
                )
                ->addColumn(
                    SouthbayReturnBalanceItemInterface::ENTITY_TOTAL_INVOICED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'identity' => false,
                        'nullable' => false,
                        'primary' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnBalanceItemInterface::ENTITY_TOTAL_RETURN,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'identity' => false,
                        'nullable' => false,
                        'primary' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnBalanceItemInterface::ENTITY_TOTAL_AVAILABLE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'identity' => false,
                        'nullable' => false,
                        'primary' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                );
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnBalanceItemInterface::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnBalanceItemInterface::TABLE),
                        [SouthbayReturnBalanceItemInterface::ENTITY_INVOICE_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnBalanceItemInterface::ENTITY_INVOICE_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnBalanceItemInterface::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnBalanceItemInterface::TABLE),
                        [SouthbayReturnBalanceItemInterface::ENTITY_INVOICE_ITEM_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [SouthbayReturnBalanceItemInterface::ENTITY_INVOICE_ITEM_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );
        }
    }

    private function createSouthbayReturnControlQaTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnControlQaInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnControlQaInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReturnControlQaInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_RETURN_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_USER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_USER_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_REAL,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_MISSING,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_EXTRA,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_ACCEPTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaInterfase::ENTITY_TOTAL_REJECT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnControlQaInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT]
                )
                ->addColumn(
                    SouthbayReturnControlQaInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnControlQaInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnControlQaInterfase::TABLE),
                        [SouthbayReturnControlQaInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnControlQaInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnControlQaInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnControlQaInterfase::TABLE),
                        [SouthbayReturnControlQaInterfase::ENTITY_RETURN_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnControlQaInterfase::ENTITY_RETURN_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }

    private function createSouthbayReturnControlQaItemTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnControlQaItemInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnControlQaItemInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReturnControlQaItemInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_RETURN_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_CONTROL_QA_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnControlQaItemInterfase::ENTITY_SKU,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnControlQaItemInterfase::ENTITY_SIZE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_RETURN,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_REAL,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_EXTRA,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_MISSING,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_ACCEPTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_QTY_REJECT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_REJECT_REASON_CODES,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnControlQaItemInterfase::ENTITY_REJECT_REASON_TEXT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnControlQaItemInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReturnControlQaItemInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnControlQaItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnControlQaItemInterfase::TABLE),
                        [SouthbayReturnControlQaItemInterfase::ENTITY_RETURN_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnControlQaItemInterfase::ENTITY_RETURN_ID]);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnControlQaItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnControlQaItemInterfase::TABLE),
                        [SouthbayReturnControlQaItemInterfase::ENTITY_CONTROL_QA_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnControlQaItemInterfase::ENTITY_CONTROL_QA_ID]);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnControlQaItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnControlQaItemInterfase::TABLE),
                        [
                            SouthbayReturnControlQaItemInterfase::ENTITY_SKU,
                            SouthbayReturnControlQaItemInterfase::ENTITY_SIZE,
                            SouthbayReturnControlQaItemInterfase::ENTITY_RETURN_ID
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        SouthbayReturnControlQaItemInterfase::ENTITY_SKU,
                        SouthbayReturnControlQaItemInterfase::ENTITY_SIZE,
                        SouthbayReturnControlQaItemInterfase::ENTITY_RETURN_ID
                    ],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE);
        }
    }

    private function createSouthbayReturnFinancialApprovalTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnFinancialApprovalInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnFinancialApprovalInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false,
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_RETURN_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_APPROVED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_USER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_USER_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_ACCEPTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_ACCEPTED_AMOUNT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_TOTAL_VALUED_AMOUNT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_EXCHANGE_RATE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReturnFinancialApprovalInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnFinancialApprovalInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnFinancialApprovalInterfase::TABLE),
                        [SouthbayReturnFinancialApprovalInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnFinancialApprovalInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnFinancialApprovalInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnFinancialApprovalInterfase::TABLE),
                        [SouthbayReturnFinancialApprovalInterfase::ENTITY_RETURN_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnFinancialApprovalInterfase::ENTITY_RETURN_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }

    private function createSouthbayReturnProductTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnProductInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnProductInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_TYPE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayReturnProductInterfase::ENTITY_CUSTOMER_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_USER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_USER_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_STATUS,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_STATUS_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_TOTAL_RETURN,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_TOTAL_ACCEPTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT_ACCEPTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_TOTAL_REJECTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_TOTAL_AMOUNT_REJECTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_PRINTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_PRINTED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_LABEL_TOTAL_PACKAGES,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReturnProductInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                        [SouthbayReturnProductInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnProductInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                        [SouthbayReturnProductInterfase::ENTITY_CUSTOMER_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnProductInterfase::ENTITY_CUSTOMER_CODE]
                );
            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                        [SouthbayReturnProductInterfase::ENTITY_CREATED_AT],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnProductInterfase::ENTITY_CREATED_AT],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductInterfase::TABLE),
                        [SouthbayReturnProductInterfase::ENTITY_CREATED_AT],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnProductInterfase::ENTITY_CREATED_AT],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }

    private function createSouthbayReturnProductItemTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnProductItemInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnProductItemInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_RETURN_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ITEM_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_SKU,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_SKU2,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_SIZE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_REASONS_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_REASONS_TEXT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    [
                        'nullable' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_NET_UNIT_PRICE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_NET_AMOUNT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_QTY,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_STATUS,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_STATUS_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_AMOUNT_ACCEPTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                        'precision' => 10,
                        'scale' => 3
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_QTY_ACCEPTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_QTY_REJECTED,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_QTY_REAL,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_QTY_EXTRA,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_QTY_MISSING,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReturnProductItemInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                        [SouthbayReturnProductItemInterfase::ENTITY_RETURN_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnProductItemInterfase::ENTITY_RETURN_ID]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                        [SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ID]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                        [SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnProductItemInterfase::ENTITY_INVOICE_ITEM_ID]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnProductItemInterfase::TABLE),
                        [
                            SouthbayReturnProductItemInterfase::ENTITY_SKU,
                            SouthbayReturnProductItemInterfase::ENTITY_SIZE,
                            SouthbayReturnProductItemInterfase::ENTITY_RETURN_ID
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [
                        SouthbayReturnProductItemInterfase::ENTITY_SKU,
                        SouthbayReturnProductItemInterfase::ENTITY_SIZE,
                        SouthbayReturnProductItemInterfase::ENTITY_RETURN_ID
                    ]
                );
        }
    }

    private function createSouthbayReturnReceptionTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnReceptionInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnReceptionInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_RETURN_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_USER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_USER_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_TOTAL_PACKAGES,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReturnReceptionInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnReceptionInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnReceptionInterfase::TABLE),
                        [SouthbayReturnReceptionInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnReceptionInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnReceptionInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnReceptionInterfase::TABLE),
                        [SouthbayReturnReceptionInterfase::ENTITY_RETURN_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnReceptionInterfase::ENTITY_RETURN_ID]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnReceptionInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnReceptionInterfase::TABLE),
                        [SouthbayReturnReceptionInterfase::ENTITY_UPDATED_AT],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnReceptionInterfase::ENTITY_UPDATED_AT]
                );
        }
    }

    private function createSouthbayReasonReturnTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReasonReturnInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReasonReturnInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReasonReturnInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayReasonReturnInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayReasonReturnInterfase::ENTITY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayReasonReturnInterfase::ENTITY_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(
                    SouthbayReasonReturnInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReasonReturnInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReasonReturnInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReasonReturnInterfase::TABLE),
                        [SouthbayReasonReturnInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReasonReturnInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReasonReturnInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReasonReturnInterfase::TABLE),
                        [SouthbayReasonReturnInterfase::ENTITY_CODE, SouthbayReasonReturnInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [SouthbayReasonReturnInterfase::ENTITY_CODE, SouthbayReasonReturnInterfase::ENTITY_COUNTRY_CODE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );
        }
    }

    private function createSouthbayReasonRejectTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReasonRejectInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReasonRejectInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReasonRejectInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayReasonRejectInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayReasonRejectInterfase::ENTITY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayReasonRejectInterfase::ENTITY_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(
                    SouthbayReasonRejectInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReasonRejectInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReasonRejectInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReasonRejectInterfase::TABLE),
                        [SouthbayReasonRejectInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReasonRejectInterfase::ENTITY_COUNTRY_CODE]
                );
        }
    }

    private function createSouthbayConfigTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayReturnConfigInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayReturnConfigInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayReturnConfigInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayReturnConfigInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayReturnConfigInterfase::ENTITY_TYPE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayReturnConfigInterfase::ENTITY_ORDER,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(SouthbayReturnConfigInterfase::ENTITY_MAX_YEAR_HISTORY,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ]
                )
                ->addColumn(SouthbayReturnConfigInterfase::ENTITY_LABEL_TEXT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    [
                        'nullable' => false
                    ]
                )
                ->addColumn(SouthbayReturnConfigInterfase::ENTITY_AVAILABLE_AUTOMATIC_APPROVAL,
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    1,
                    [
                        'nullable' => true,
                        'default' => 0
                    ],
                )
                ->addColumn(SouthbayReturnConfigInterfase::ENTITY_MAX_AUTOMATIC_AMOUNT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => true, 'unsigned' => true, 'precision' => 10, 'scale' => 2]
                )
                ->addColumn(
                    SouthbayReasonRejectInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayReasonRejectInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnConfigInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnConfigInterfase::TABLE),
                        [SouthbayReturnConfigInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayReturnConfigInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayReturnConfigInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayReturnConfigInterfase::TABLE),
                        [SouthbayReturnConfigInterfase::ENTITY_TYPE, SouthbayReturnConfigInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [SouthbayReturnConfigInterfase::ENTITY_TYPE, SouthbayReturnConfigInterfase::ENTITY_COUNTRY_CODE],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );
        }
    }

    private function createSouthbayExchangeReturnTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayExchangeReturnInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayExchangeReturnInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayExchangeReturnInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayExchangeReturnInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayExchangeReturnInterfase::ENTITY_EXCHANGE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => false, 'unsigned' => true, 'precision' => 10, 'scale' => 3]
                )
                ->addColumn(SouthbayExchangeReturnInterfase::ENTITY_USER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false]
                )
                ->addColumn(SouthbayExchangeReturnInterfase::ENTITY_USER_NAME,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['nullable' => false]
                )
                ->addColumn(
                    SouthbayExchangeReturnInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayExchangeReturnInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayExchangeReturnInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayExchangeReturnInterfase::TABLE),
                        [SouthbayExchangeReturnInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayExchangeReturnInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayExchangeReturnInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayExchangeReturnInterfase::TABLE),
                        [SouthbayExchangeReturnInterfase::ENTITY_CREATED_AT, SouthbayExchangeReturnInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayExchangeReturnInterfase::ENTITY_CREATED_AT, SouthbayExchangeReturnInterfase::ENTITY_COUNTRY_CODE]
                );
        }
    }

    private function createSouthbayRolConfigRtvTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayRolConfigRtvInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayRolConfigRtvInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayRolConfigRtvInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayRolConfigRtvInterfase::ENTITY_TYPE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayRolConfigRtvInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayRolConfigRtvInterfase::ENTITY_TYPE_ROL,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayRolConfigRtvInterfase::ENTITY_ROL_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayRolConfigRtvInterfase::ENTITY_APPROVAL_USE_AMOUNT_LIMIT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    1,
                    [
                        'nullable' => false,
                        'default' => 0
                    ],
                )
                ->addColumn(SouthbayRolConfigRtvInterfase::ENTITY_APPROVAL_AMOUNT_LIMIT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => false, 'unsigned' => true, 'precision' => 10, 'scale' => 3, 'default' => 0]
                )
                ->addColumn(
                    SouthbayRolConfigRtvInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayRolConfigRtvInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayRolConfigRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayRolConfigRtvInterfase::TABLE),
                        [SouthbayRolConfigRtvInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayRolConfigRtvInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayRolConfigRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayRolConfigRtvInterfase::TABLE),
                        [SouthbayRolConfigRtvInterfase::ENTITY_TYPE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayRolConfigRtvInterfase::ENTITY_TYPE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayRolConfigRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayRolConfigRtvInterfase::TABLE),
                        [SouthbayRolConfigRtvInterfase::ENTITY_TYPE_ROL, SouthbayRolConfigRtvInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayRolConfigRtvInterfase::ENTITY_TYPE_ROL, SouthbayRolConfigRtvInterfase::ENTITY_COUNTRY_CODE]
                );
        }
    }

    private function createSouthbayConfigNotificationRtvTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayConfigNotificationRtvInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayConfigNotificationRtvInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayConfigNotificationRtvInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayConfigNotificationRtvInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayConfigNotificationRtvInterfase::ENTITY_TYPE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayConfigNotificationRtvInterfase::ENTITY_TEMPLATE_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayConfigNotificationRtvInterfase::ENTITY_STATUS,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(
                    SouthbayConfigNotificationRtvInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayConfigNotificationRtvInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayConfigNotificationRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayConfigNotificationRtvInterfase::TABLE),
                        [SouthbayConfigNotificationRtvInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayConfigNotificationRtvInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayConfigNotificationRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayConfigNotificationRtvInterfase::TABLE),
                        [SouthbayConfigNotificationRtvInterfase::ENTITY_TYPE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayConfigNotificationRtvInterfase::ENTITY_TYPE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayConfigNotificationRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayConfigNotificationRtvInterfase::TABLE),
                        [SouthbayConfigNotificationRtvInterfase::ENTITY_STATUS, SouthbayConfigNotificationRtvInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayConfigNotificationRtvInterfase::ENTITY_STATUS, SouthbayConfigNotificationRtvInterfase::ENTITY_COUNTRY_CODE]
                );
        }
    }

    private function createSouthbayConfigNotificationRtvByRolTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayConfigNotificationRtvByRolInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayConfigNotificationRtvByRolInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayConfigNotificationRtvByRolInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayConfigNotificationRtvByRolInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayConfigNotificationRtvByRolInterfase::ENTITY_TYPE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayConfigNotificationRtvByRolInterfase::ENTITY_TYPE_ROL,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayConfigNotificationRtvByRolInterfase::ENTITY_TEMPLATE_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayConfigNotificationRtvByRolInterfase::ENTITY_STATUS,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(
                    SouthbayConfigNotificationRtvByRolInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayConfigNotificationRtvByRolInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayConfigNotificationRtvByRolInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayConfigNotificationRtvByRolInterfase::TABLE),
                        [SouthbayConfigNotificationRtvByRolInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayConfigNotificationRtvByRolInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayConfigNotificationRtvByRolInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayConfigNotificationRtvByRolInterfase::TABLE),
                        [SouthbayConfigNotificationRtvByRolInterfase::ENTITY_TYPE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayConfigNotificationRtvByRolInterfase::ENTITY_TYPE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayConfigNotificationRtvByRolInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayConfigNotificationRtvByRolInterfase::TABLE),
                        [SouthbayConfigNotificationRtvByRolInterfase::ENTITY_STATUS, SouthbayConfigNotificationRtvByRolInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayConfigNotificationRtvByRolInterfase::ENTITY_STATUS, SouthbayConfigNotificationRtvByRolInterfase::ENTITY_COUNTRY_CODE]
                );
        }
    }

    private function createSouthbayNotificationHistoryRtvTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(SouthbayNotificationHistoryRtvInterfase::TABLE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE)
                )
                ->addColumn(
                    SouthbayNotificationHistoryRtvInterfase::ENTITY_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_COUNTRY_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_TYPE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_RETURN_ID,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_CUSTOMER_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_TEMPLATE_CODE,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_SUBJECT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_CONTENT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    500,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_FROM,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(SouthbayNotificationHistoryRtvInterfase::ENTITY_TO,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    150,
                    [
                        'nullable' => false
                    ],
                )
                ->addColumn(
                    SouthbayNotificationHistoryRtvInterfase::ENTITY_CREATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    SouthbayNotificationHistoryRtvInterfase::ENTITY_UPDATED_AT,
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At');

            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_TYPE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_TYPE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_RETURN_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_RETURN_ID]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_CUSTOMER_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_CUSTOMER_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_STATUS, SouthbayNotificationHistoryRtvInterfase::ENTITY_COUNTRY_CODE]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_CREATED_AT],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_CREATED_AT]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                    $setup->getIdxName(
                        $setup->getTable(SouthbayNotificationHistoryRtvInterfase::TABLE),
                        [SouthbayNotificationHistoryRtvInterfase::ENTITY_TO],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayNotificationHistoryRtvInterfase::ENTITY_TO]
                );
        }
    }
}
