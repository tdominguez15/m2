<?php

namespace Southbay\Product\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    const TABLE_SEASON_TYPE = 'southbay_season_type';
    const TABLE_SEASON = 'southbay_season';

    const TABLE_SEASON_IMPORT_PRODUCTS_HISTORY = 'southbay_import_products_history';

    const TABLE_SEASON_IMPORT_PRODUCTS_DETAIL = 'southbay_import_products_detail';
    const TABLE_PRODUCT_SAP_INTERFACE = 'southbay_product_sap_interface';
    const TABLE_OWNER_GROUP = 'southbay_owner_group';


    /**
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createTypeOfSeasonsEntity($setup);
        $this->createSeasonEntity($setup);
        $this->createSeasonImportProductsHistoryEntity($setup);
        $this->createProductSapInterfaceEntity($setup);
        $this->createOwnerGroupEntity($setup);

        $setup->endSetup();
    }
    private function createProductSapInterfaceEntity(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(InstallSchema::TABLE_PRODUCT_SAP_INTERFACE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(InstallSchema::TABLE_PRODUCT_SAP_INTERFACE)
                )
                ->addColumn(
                    'southbay_product_sap_id',
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
                ->addColumn('southbay_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Southbay Data'
                )
                ->addColumn('southbay_status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'Southbay Status'
                )
                ->addColumn('southbat_result_msg',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['nullable' => true],
                    'Southbat Result Message'
                )
                ->addColumn(
                    'start_import_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'Start Import Date'
                )
                ->addColumn(
                    'end_import_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'End Import Date'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Southbay Product SAP Interface Table');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(InstallSchema::TABLE_PRODUCT_SAP_INTERFACE),
                    $setup->getIdxName(
                        $setup->getTable(InstallSchema::TABLE_PRODUCT_SAP_INTERFACE),
                        ['southbay_status'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['southbay_status'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }

    private function createTypeOfSeasonsEntity(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(InstallSchema::TABLE_SEASON_TYPE)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(InstallSchema::TABLE_SEASON_TYPE)
                )
                ->addColumn(
                    'type_id',
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
                ->addColumn('type_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'Code'
                )
                ->addColumn('type_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false],
                    'Name'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Southbay Seasons Types Table');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addIndex(
                $setup->getTable(InstallSchema::TABLE_SEASON_TYPE),
                $setup->getIdxName(
                    $setup->getTable(InstallSchema::TABLE_SEASON_TYPE),
                    ['type_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['type_code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );
        }
    }

    private function createSeasonEntity(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(InstallSchema::TABLE_SEASON)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(InstallSchema::TABLE_SEASON)
                )
                ->addColumn(
                    'season_id',
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
                ->addColumn('season_type_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn('season_category_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => false],
                    'Category id'
                )
                ->addColumn('season_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'Code'
                )
                ->addColumn('season_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false],
                    'Name'
                )
                ->addColumn('season_description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => true],
                    'Description'
                )
                ->addColumn('season_enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Habilitada/Deshabilitada'
                )
                ->addColumn(
                    'start_load_catalog_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Start load customer catalog date'
                )
                ->addColumn(
                    'end_load_catalog_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'End load customer catalog date'
                )
                ->addColumn(
                    'purchase_start_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Start customer purchase date'
                )
                ->addColumn(
                    'purchase_end_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'End customer purchase date'
                )
                ->addColumn(
                    'month_delivery_date_1',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    'month_delivery_date_2',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true]
                )
                ->addColumn(
                    'month_delivery_date_3',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true]
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Southbay Seasons Table');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(InstallSchema::TABLE_SEASON),
                    $setup->getIdxName(
                        $setup->getTable(InstallSchema::TABLE_SEASON),
                        ['season_code'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['season_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(InstallSchema::TABLE_SEASON),
                    $setup->getIdxName(
                        $setup->getTable(InstallSchema::TABLE_SEASON),
                        ['season_enabled'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['season_enabled'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }

    private function createSeasonImportProductsHistoryEntity(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(InstallSchema::TABLE_SEASON_IMPORT_PRODUCTS_HISTORY)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(InstallSchema::TABLE_SEASON_IMPORT_PRODUCTS_HISTORY)
                )
                ->addColumn(
                    'season_import_id',
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
                ->addColumn('season_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => false],
                    'Season Id'
                )
                ->addColumn('file',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['nullable' => false],
                    'File'
                )
                ->addColumn('status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'Import status (pending|running|complete|error)'
                )
                ->addColumn('result_msg',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    250,
                    ['nullable' => true],
                    'Import result msg'
                )
                ->addColumn(
                    'start_import_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'Start import file date'
                )
                ->addColumn(
                    'end_import_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'End import file date'
                )
                ->addColumn(
                    'lines',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unsigned' => true],
                    'End customer purchase date'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Southbay Seasons Import Products Table');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(InstallSchema::TABLE_SEASON_IMPORT_PRODUCTS_HISTORY),
                    $setup->getIdxName(
                        $setup->getTable(InstallSchema::TABLE_SEASON_IMPORT_PRODUCTS_HISTORY),
                        ['season_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['season_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(InstallSchema::TABLE_SEASON_IMPORT_PRODUCTS_HISTORY),
                    $setup->getIdxName(
                        $setup->getTable(InstallSchema::TABLE_SEASON_IMPORT_PRODUCTS_HISTORY),
                        ['end_import_date'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['end_import_date'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }

    private function createOwnerGroupEntity(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(InstallSchema::TABLE_OWNER_GROUP)) {
            $table = $setup->getConnection()
                ->newTable($setup
                    ->getTable(InstallSchema::TABLE_OWNER_GROUP)
                )
                ->addColumn(
                    'owner_group_id',
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
                ->addColumn('country_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'SAP Country Code'
                )
                ->addColumn('owner_group_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'SAP Code'
                )
                ->addColumn('owner_group_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false],
                    'SAP Client Name'
                )
                ->addColumn('owner_group_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => false],
                    'SAP Client Name'
                )
                ->addColumn('owner_group_segmentation',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => true],
                    'Product segmentations'
                )
                ->addColumn('owner_group_tier',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    200,
                    ['nullable' => true],
                    'Product tier'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Southbay Owner Group');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable(InstallSchema::TABLE_OWNER_GROUP),
                    $setup->getIdxName(
                        $setup->getTable(InstallSchema::TABLE_OWNER_GROUP),
                        ['owner_group_code'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['owner_group_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                );
        }
    }
}
