<?php

namespace Southbay\Product\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Southbay\Product\Api\Data\SeasonInterface;
use Southbay\Product\Api\Data\SouthbayProductImportHistoryInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    const TABLE_SEASON_IMPORT_PRODUCTS_HISTORY = 'southbay_import_products_history';

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $this->upgradeSeasonTable($setup);
            $this->upgradeImportProductsHistory($setup);
        }
        $setup->endSetup();
    }

    private function upgradeImportProductsHistory(SchemaSetupInterface $setup)
    {
        $conn = $setup->getConnection();
        $table = $setup->getTable(self::TABLE_SEASON_IMPORT_PRODUCTS_HISTORY);
        if ($conn->isTableExists($table)) {
            $conn->addColumn(
                $table,
                'start_on_line_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'start on line number'
                ]);

            $conn
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [SouthbayProductImportHistoryInterface::ENTITY_STATUS],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SouthbayProductImportHistoryInterface::ENTITY_STATUS]
                );
        }
    }

    private function upgradeSeasonTable(SchemaSetupInterface $setup)
    {
        $conn = $setup->getConnection();
        $table = $setup->getTable(SeasonInterface::TABLE);
        if ($conn->isTableExists($table)) {
            $conn->dropIndex($table, $setup->getIdxName(
                $setup->getTable(SeasonInterface::TABLE),
                ['season_enabled'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ));
            $conn->dropColumn($table, 'season_enabled');
            $conn->dropColumn($table, 'start_load_catalog_date');
            $conn->dropColumn($table, 'end_load_catalog_date');
            $conn->dropColumn($table, 'purchase_start_date');
            $conn->dropColumn($table, 'purchase_end_date');

            $conn->addColumn($table, SeasonInterface::ENTITY_SEASON_STORE_ID, [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'length' => null,
                'nullable' => true,
                'comment' => 'store id'
            ]);

            $conn->addColumn($table, SeasonInterface::ENTITY_SEASON_COUNTRY_CODE, [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'comment' => 'country code'
            ]);

            $conn->addColumn($table, SeasonInterface::ENTITY_SEASON_START_AT, [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'nullable' => true,
                'comment' => 'season start at'
            ]);

            $conn->addColumn($table, SeasonInterface::ENTITY_SEASON_END_AT, [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'nullable' => true,
                'comment' => 'season end at'
            ]);

            $conn->addColumn($table, SeasonInterface::ENTITY_SEASON_ACTIVE, [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'season active'
            ]);

            $conn
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [SeasonInterface::ENTITY_SEASON_ACTIVE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SeasonInterface::ENTITY_SEASON_ACTIVE]
                );

            $conn
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [SeasonInterface::ENTITY_SEASON_STORE_ID, SeasonInterface::ENTITY_SEASON_ACTIVE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SeasonInterface::ENTITY_SEASON_ACTIVE]
                );

            $conn
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [SeasonInterface::ENTITY_SEASON_TYPE_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SeasonInterface::ENTITY_SEASON_TYPE_CODE]
                );

            $conn
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [SeasonInterface::ENTITY_SEASON_START_AT, SeasonInterface::ENTITY_SEASON_END_AT],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SeasonInterface::ENTITY_SEASON_START_AT, SeasonInterface::ENTITY_SEASON_END_AT]
                );

            $conn
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [SeasonInterface::ENTITY_SEASON_STORE_ID],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SeasonInterface::ENTITY_SEASON_STORE_ID]
                );

            $conn
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        [SeasonInterface::ENTITY_SEASON_COUNTRY_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [SeasonInterface::ENTITY_SEASON_COUNTRY_CODE]
                );
        }
    }
}
