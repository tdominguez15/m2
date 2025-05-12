<?php

namespace Southbay\Product\Model\Import;

class ProductImporterUtil
{
    public static function getAttributes()
    {
        return [
            'southbay_department' => ['frontend_label' => 'Departamento', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => self::transforToOptionValues(self::departmentCodesText(), 'southbay_department')]],
            'southbay_gender' => ['frontend_label' => 'Genero', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => self::transforToOptionValues(self::genderCodesText(), 'southbay_gender')]],
            'southbay_age' => ['frontend_label' => 'Edad', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => self::transforToOptionValues(self::ageCodesText(), 'southbay_age')]],
            'southbay_sport' => ['frontend_label' => 'Deporte', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => self::transforToOptionValues(self::sportCodesText(), 'southbay_sport')]],
            'southbay_size' => ['frontend_label' => 'Talle', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select', 'option' => ['value' => []]],
            'southbay_color' => ['frontend_label' => 'Color', 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select', 'option' => ['value' => []]],
            'southbay_season_code' => [
                'frontend_label' => 'Temporada',
                'is_required' => 0,
                'is_used_in_grid' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
            'southbay_silueta_1' => ['frontend_label' => 'Silueta 1', 'is_required' => 0, 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => self::transforToOptionValues(self::firstSiluetaCodesText(), 'southbay_silueta_1')]],
            'southbay_silueta_2' => ['frontend_label' => 'Silueta 2', 'is_required' => 0, 'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table', 'frontend_input' => 'select',
                'option' => ['value' => self::transforToOptionValues(self::secondSiluetaText(), 'southbay_silueta_2')]],
            'southbay_channel_level_list' => [
                'frontend_label' => 'Canal y Nivel',
                'is_used_in_grid' => 0,
                'is_required' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
            'southbay_price_retail' => [
                'frontend_label' => 'Precio Minorista',
                'backend_type' => 'decimal',
                'is_used_in_grid' => 0,
                'is_required' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
            'southbay_purchase_unit' => [
                'backend_type' => 'int',
                'frontend_label' => 'Unidad de Compra',
                'is_used_in_grid' => 0,
                'is_required' => 0,
                'is_used_for_promo_rules' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 0,
                'is_used_for_price_rules' => 0,
                'is_wysiwyg_enabled' => 0,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'used_for_sort_by' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0
            ],
        ];
    }

    private static function transforToOptionValues($list, $code)
    {
        $result = [];

        foreach ($list as $key => $text) {
            $result[$code . '_' . $key] = [$text];
        }

        return $result;
    }

    private static function departmentCodes(): array
    {
        return [
            'V', 'W', 'X', 'Y', 'Z'
        ];
    }

    private static function departmentCodesText(): array
    {
        return [
            'V' => 'CALZADO',
            'W' => 'ROPA',
            'X' => 'ACCESORIOS',
            'Y' => 'TECNOLOGIA',
            'Z' => 'MISCELANEO'
        ];
    }

    private static function genderCodes(): array
    {
        return [
            '01', '02', '03'
        ];
    }

    private static function genderCodesText(): array
    {
        return [
            '01' => 'MASCULINO', '02' => 'FEMENINO', '03' => 'UNISEX'
        ];
    }

    private static function ageCodes(): array
    {
        return [
            '01', '02', '03', '04', '05'
        ];
    }

    private static function ageCodesText(): array
    {
        return [
            '01' => 'ADULTO', '02' => 'JOVEN', '03' => 'PREESCOLAR', '04' => 'INFANTE', '05' => 'GENERICO'
        ];
    }

    private static function firstSiluetaCodes(): array
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52'
        ];
    }

    private static function firstSiluetaCodesText(): array
    {
        return [
            '01' => 'ABRIGO', '02' => 'BALONES', '03' => 'BASCULA', '04' => 'BOCINAS', '05' => 'BOLSOS', '06' => 'BOTAS', '07' => 'BRA', '08' => 'CAMISETA O REMERA', '09' => 'CHALECO', '10' => 'COLCHONETA', '11' => 'CONJUNTO', '12' => 'CONSUMO', '13' => 'EJERCITADOR', '14' => 'ENTERIZO', '15' => 'FALDA', '16' => 'GORRAS', '17' => 'GORROS', '18' => 'GUANTES', '19' => 'LENTES', '20' => 'LIMPIEZA', '21' => 'MEDIAS', '22' => 'MOCHILAS', '23' => 'PANTALON', '24' => 'PELOTA', '25' => 'PESAS', '26' => 'POLO', '27' => 'PROTECTORES', '28' => 'RELOJES', '29' => 'REPLICAS O JERSEYS', '30' => 'SANDALIAS', '31' => 'TACOS', '32' => 'TACOS SUELA DE GOMA', '33' => 'TAQUILLOS', '34' => 'TERMOS', '35' => 'TRAJE DE BAÑO', '36' => 'VESTIDO', '37' => 'ZAPATILLAS', '38' => 'ZAPATOS', '39' => 'MALETA', '40' => 'SAUNA', '41' => 'HORMADOR', '42' => 'TOALLAS', '43' => 'CORREA', '44' => 'AUDIFONOS', '45' => 'PLANTILLAS', '46' => 'BODIES', '47' => 'INTERIORES', '48' => 'OTROS', '49' => 'FLOTADOR', '50' => 'CHAPALETA', '51' => 'FUNDA', '52' => 'SERVICIOS'
        ];
    }

    private static function sportCodes(): array
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24'
        ];
    }

    private static function sportCodesText(): array
    {
        return [
            '01' => 'BALONCESTO', '02' => 'BEISBOL', '03' => 'BLUETOOTH', '04' => 'BOXEO', '05' => 'CASUAL', '06' => 'CICLISMO', '07' => 'CORRER', '08' => 'ENTRENAMIENTO', '09' => 'ESCOLARES', '10' => 'FUT AMERICANO', '11' => 'FUTBOL', '12' => 'GOLF', '13' => 'NATACION', '14' => 'PADEL', '15' => 'RUGBY', '16' => 'SKATE', '17' => 'TENIS', '18' => 'TRAIL', '19' => 'UNIFORME', '20' => 'VOLEIBOL', '21' => 'YOGA', '22' => 'INTELIGENTE', '23' => 'ATLETISMO', '24' => 'BAILE'
        ];
    }

    private static function secondSilueta(): array
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79', '80', '81', '82', '83', '84', '85', '86', '87', '88', '89', '90', '91', '92', '93'
        ];
    }

    private static function secondSiluetaText(): array
    {
        return [
            '01' => '3/4 ALTAS', '02' => 'ALTAS', '03' => 'BAJAS', '04' => 'CON CAPUCHA', '05' => 'SIN CAPUCHA', '06' => 'ZIPPER 1/4', '07' => 'ACOLCHADO', '08' => 'NO ACOLCHADO', '09' => 'SOPORTE ALTO', '10' => 'SOPORTE BAJO', '11' => 'SOPORTE MEDIO', '12' => 'CRUZADO', '13' => 'MANGA CORTA', '14' => 'MANGA LARGA', '15' => 'SIN MANGAS', '16' => 'DOS PIEZAS', '17' => 'UNA PIEZA', '18' => 'CORTO', '19' => 'LARGO', '20' => 'DOS EN UNO', '21' => '5" CORTO', '22' => '6" CORTO', '23' => '7" CORTO', '24' => 'CORTO A LA RODILLA', '25' => 'LARGO AJUSTADO', '26' => 'TRES CUARTOS (3/4)', '27' => 'TRES CUARTOS (3/4) AJUSTADO', '28' => 'DOS EN UNO', '29' => 'BIKER', '30' => 'CORTO DOS EN UNO', '31' => 'CORTO AJUSTADO', '32' => 'MINI', '33' => 'ELIPTICO', '34' => 'REDONDO', '35' => 'TAQUERA', '36' => 'CANGURERA', '37' => 'CRUZADO', '38' => 'CARTERA', '39' => 'MENSAJERO', '40' => 'BATERA', '41' => 'NECESER', '42' => 'ESTERILLA', '43' => 'BARRA ENERGETICA', '44' => 'BEBIDA ENERGETICA', '45' => 'CUERDA DE SALTAR', '46' => 'LIGAS RESISTENCIA', '47' => 'CINTURON', '48' => 'BASE PARA FLEXIONES', '49' => 'VENDAS BOXEO', '50' => 'EMPUÑADURA', '51' => 'BANDAS DE RESISTENCIA', '52' => 'AJUSTABLES', '53' => 'CERRADAS', '54' => 'VISERA', '55' => 'PESCADOR', '56' => 'BEANIE', '57' => 'SOL', '58' => 'KIT', '59' => 'CAPA DURA', '60' => 'CAPA FLEXIBLE', '61' => 'INVISIBLES', '62' => 'TOBILLERA', '63' => 'LARGAS', '64' => 'ALTAS A LA RODILLA', '65' => 'SACO DE GIMNASIA', '66' => 'MANCUERNA', '67' => 'RUSAS', '68' => 'AJUSTABLE MUÑECA', '69' => 'AJUSTABLE TOBILLO', '70' => 'NARICERA', '71' => 'TAPONES DE OIDO', '72' => 'RODILLERAS', '73' => 'MENISQUERA', '74' => 'MUÑEQUERA', '75' => 'CODERAS', '76' => 'BUCALES', '77' => 'CASCOS', '78' => 'BARBILLA', '79' => 'ESPINILLERA', '80' => 'FAJA', '81' => 'PLASTICO', '82' => 'METAL', '83' => 'RODILLOS', '84' => 'AUTOMATICA', '85' => 'SILICON', '86' => 'PROTECCIÓN', '87' => 'CLASICAS', '88' => 'DEPORTIVAS', '89' => 'INALAMBRICO', '90' => 'CON CABLE', '91' => 'MALETIN', '92' => 'MANGA', '93' => 'HEADBANDS'
        ];
    }

    private static function seasonTypes(): array
    {
        return [
            ['code' => '001', 'name' => 'Spring'],
            ['code' => '002', 'name' => 'Summer'],
            ['code' => '003', 'name' => 'Fall'],
            ['code' => '004', 'name' => 'Holliday'],
            ['code' => '005', 'name' => 'Sin Temporada'],
            ['code' => '006', 'name' => 'Carry Over'],
            ['code' => '999', 'name' => 'N/A']
        ];
    }

    public static function getDefaultAttributeConfig(): array
    {
        return [
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_global' => 1,
            'attribute_group_name' => 'Southbay',
            'frontend_input' => 'text',
            'is_user_defined' => 1,
            'is_unique' => 0,
            'is_required' => 1,
            'is_used_for_promo_rules' => 1,
            'is_used_in_grid' => 1,
            'is_searchable' => 0,
            'is_comparable' => 0,
            'is_visible_in_advanced_search' => 1,
            'is_visible_in_grid' => 1,
            'is_filterable_in_grid' => 1,
            'is_used_for_price_rules' => 0,
            'is_wysiwyg_enabled' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 1,
            'used_in_product_listing' => 1,
            'used_for_sort_by' => 1,
            'is_filterable' => 1,
            'is_filterable_in_search' => 1,
            'backend_type' => 'varchar'
        ];
    }

    public static function getAttributeSetName()
    {
        return 'southbay_attrs';
    }
}
