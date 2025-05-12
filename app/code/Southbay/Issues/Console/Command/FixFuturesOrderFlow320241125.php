<?php

namespace Southbay\Issues\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixFuturesOrderFlow320241125 extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:fix:future:orders:20241125')
            ->addArgument('store_id')
            ->addArgument('flow')
            ->addArgument('folder')
//            ->addArgument('file')
            ->setDescription('Fix-20241125: los productos que son de flow 3 no estaban siendo agregado al cart');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store_id = $input->getArgument('store_id');
        $folder = $input->getArgument('folder');
        $flow = $input->getArgument('flow');

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var \Southbay\Product\Helper\Data $helper
         */
        $helper = $objectManager->get('Southbay\Product\Helper\Data');
        /**
         * @var \Southbay\CustomCheckout\Helper\UploadCardData $helper2
         */
        $helper2 = $objectManager->get('Southbay\CustomCheckout\Helper\UploadCardData');

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql = "select order_id, sku, qty_ordered from sales_order_item where product_type = 'configurable'";
        $items = $connection->fetchAll($sql);

        $map = [];

        foreach ($items as $item) {
            if (!isset($map[$item['order_id']])) {
                $map[$item['order_id']] = [];
            }

            $map[$item['order_id']][$item['sku']] = $item['qty_ordered'];
        }

        $collectionFactory = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        $orders_id = array_keys($map);

        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection
         */
        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orders_id]);
        $collection->addFieldToFilter('store_id', ['eq' => $store_id]);
        $collection->addFieldToFilter('status', ['eq' => 'pending']);
        $orders = $collection->getItems();

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        foreach ($orders as $order) {
            $items = $order->getItems();
            $map_skus = $map[$order->getId()];
            foreach ($items as $item) {
                if (isset($map_skus[$item->getSku()])) {
                    $product = $helper2->findProductBySku($item->getSku(), $store_id);
                    $variant = $helper->getFirstProductVariant($product);

                    $item->setSku($variant->getSku());
                    $item->setProductType($variant->getTypeId());
                    $item->setProductId($variant->getId());
                    $item->setStoreId($store_id);
                    $item->setName($variant->getName());

                    $item->setPrice($variant->getPrice());
                    $item->setOriginalPrice($variant->getPrice());
                    $item->setBasePrice($variant->getPrice());
                    $item->setBaseOriginalPrice($variant->getPrice());
                    $item->setPriceInclTax($variant->getPrice());
                    $item->setBasePriceInclTax($variant->getPrice());

                    $item->setRowTotal($variant->getPrice() * $item->getQtyOrdered());
                    $item->setBaseRowTotal($item->getRowTotal());
                    $item->setRowTotalInclTax($item->getRowTotal());
                    $item->setBaseRowTotalInclTax($item->getRowTotal());

                    $order->save();
                }
            }
        }

        return 1;
    }

    protected function executev2(InputInterface $input, OutputInterface $output)
    {
        $items = [
            // ['order' => '2000000606', 'file' => 'PLANILLA_PEDIDO_HO_25_FTW_CORDOBA__1.xlsx'], ['order' => '2000000607', 'file' => 'PLANILLA_HO25_APP_NSP_.xlsx'], ['order' => '2000000608', 'file' => 'PLANILLA_HO25_EQ_NSP.xlsx'], ['order' => '2000000609', 'file' => 'PLANILLA_HO25_FTW_NSP_.xlsx'], ['order' => '2000000610', 'file' => 'FA25_HO25_APP_NBHD_ARG_BA.xlsx'], ['order' => '2000000611', 'file' => 'FA25_HO25_APP_NBHD_ARG.xlsx'], ['order' => '2000000612', 'file' => 'FA25_HO25_APP_SG_ARG.xlsx'], ['order' => '2000000613', 'file' => 'FA25_HO25_APP_CS_NIKE_SB_ARG.xlsx'], ['order' => '2000000614', 'file' => 'FA25_HO25_APP_AS_ARG.xlsx'], ['order' => '2000000615', 'file' => 'FA25_HO25_EQ_AS_ARG.xlsx'], ['order' => '2000000616', 'file' => 'FA25_HO25_EQ_NBHD_ARG.xlsx'], ['order' => '2000000617', 'file' => 'FA25_HO25_EQ_SG_ARG.xlsx'], ['order' => '2000000618', 'file' => 'FA25_HO25_DABRA_-_DX_DEPOSITO_4.xlsx'], ['order' => '2000000619', 'file' => 'FA25_HO25_EQ_SG_ARG_2.xlsx'], ['order' => '2000000620', 'file' => 'FA25_HO25_APP_SG_ARG_1.xlsx'], ['order' => '2000000621', 'file' => 'FA25_HO25_FTW_SG_ARG.xlsx'], ['order' => '2000000622', 'file' => 'Planilla_de_pedido_indumentaria.xlsx'], ['order' => '2000000623', 'file' => 'Planilla_de_pedido_calzado_1.xlsx'], ['order' => '2000000624', 'file' => 'FA25_HO25_FTW_SG_ARG_3.xlsx'], ['order' => '2000000625', 'file' => 'FA25_HO25_FTW_AS_ARG.xlsx'], ['order' => '2000000626', 'file' => 'PEDIDO_HANSSACHS_4T2025_CALZ_FA25_HO25_FTW_SG_ARG.xlsx'], ['order' => '2000000627', 'file' => 'PEDIDO_VIEGAS_4T2025_CALZ_FA25_HO25_FTW_SG_ARG.xlsx'], ['order' => '2000000628', 'file' => 'FA25_HO25_DABRA_-_DX_DIGITAL_1.xlsx'], ['order' => '2000000629', 'file' => 'FA25_HO25_APP_CS_NIKE_SB_ARG_20_11_2024.xlsx'], ['order' => '2000000630', 'file' => 'FA25_HO25_APP_SG_ARG_Toyos_20_11_24.xlsx'], ['order' => '2000000631', 'file' => 'FA25_HO25_EQ_SG_ARG_Toyos_20_11_2024.xlsx'], ['order' => '2000000632', 'file' => 'FA25_HO25_FTW_AS_ARG_Cero26_modificado.xlsx'], ['order' => '2000000633', 'file' => 'FA25_HO25_DABRA_-_DX_M_2.xlsx'], ['order' => '2000000634', 'file' => 'FA25_HO25_APP_AS_ARG_Cero26.xlsx'], ['order' => '2000000635', 'file' => 'FA25_HO25_EQ_AS_ARG_Cero26_modificado.xlsx'], ['order' => '2000000637', 'file' => 'FA25_HO25_DABRA_-_DX_M_3.xlsx'], ['order' => '2000000638', 'file' => 'FA25_HO25_FTW_NBHD_ARG_Fuencarral.xlsx'], ['order' => '2000000640', 'file' => 'FA25_HO25_APP_NBHD_ARG_Fuencarral.xlsx'], ['order' => '2000000641', 'file' => 'FA25_HO25_EQ_NBHD_ARG_Fuencarral.xlsx'], ['order' => '2000000642', 'file' => 'FA25_HO25_DABRA_-_DX_M_4.xlsx'], ['order' => '2000000643', 'file' => 'FA25_HO25_FTW_NBHD_ARG_DIO_BS_AS_OK.xlsx'], ['order' => '2000000644', 'file' => 'FA25_HO25_FTW_CS_NIKE_SB_ARG_OK_PARA_SUBIR.xlsx'], ['order' => '2000000646', 'file' => 'FA25_HO25_FTW_AS_ARG_OK_PARA_MANDAR.xlsx'], ['order' => '2000000647', 'file' => 'VIEGAS_INDM_4T25_FA25_HO25_APP_SG_ARG.xlsx'], ['order' => '2000000648', 'file' => 'HANSSACHS_INDM_4T25_FA25_HO25_APP_SG_ARG.xlsx'], ['order' => '2000000649', 'file' => 'FA25_HO25_FTW_NBHD_ARG_DIO_ROS_OK_1.xlsx'], ['order' => '2000000650', 'file' => 'FA25_HO25_FTW_AS_ARG_Cero26_AJ1.xlsx'], ['order' => '2000000651', 'file' => 'PLANILLA_HO25_FTW_2_NSP.xlsx'], ['order' => '2000000652', 'file' => 'FA25_HO25_APP_NSP_ARG.xlsx'], ['order' => '2000000653', 'file' => 'FA25_HO25_FTW_CS_FTBL_ARG_TDF_Unicenter_1.xlsx'], ['order' => '2000000655', 'file' => 'FA25_HO25_APP_SG_ARG_3.xlsx'], ['order' => '2000000656', 'file' => 'FA25_HO25_APP_SG_ARG_mdq_le_sport_1.xlsx'], ['order' => '2000000657', 'file' => 'FA25_HO25_APP_SG_ARG_stadio_uno.xlsx'], ['order' => '2000000658', 'file' => 'FA25_HO25_APP_AS_ARG_mdq_le_sport.xlsx'], ['order' => '2000000659', 'file' => 'FA25_HO25_APP_AS_ARG_stadio_uno.xlsx'], ['order' => '2000000660', 'file' => 'FA25_HO25_APP_CS_RUN_ARG.xlsx'], ['order' => '2000000662', 'file' => 'FA25_HO25_FTW_CS_FTBL_ARG_TDF_Abasto.xlsx'], ['order' => '2000000664', 'file' => 'FA25_HO25_FTW_NSP_ARG_5.xlsx'], ['order' => '2000000665', 'file' => 'FA25_HO25_FTW_CS_FTBL_ARG_TDF_Cordoba.xlsx'], ['order' => '2000000667', 'file' => 'PLANILLA_HO25_FTW_NSP_3_.xlsx'], ['order' => '2000000669', 'file' => 'FA25_HO25_APP_CS_FTBL_ARG_TDF_Unicenter.xlsx'], ['order' => '2000000671', 'file' => 'FA25_HO25_APP_CS_FTBL_ARG_TDF_Abasto.xlsx'], ['order' => '2000000672', 'file' => 'FA25_HO25_APP_CS_FTBL_ARG_TDF_Cordoba.xlsx'], ['order' => '2000000673', 'file' => 'FA25_HO25_EQ_CS_FTBL_ARG_TDF_Unicenter.xlsx'], ['order' => '2000000674', 'file' => 'VIEGAS_HANSSACHS_4T_ACC25_FA25_HO25_EQ_SG_ARG.xlsx'], ['order' => '2000000675', 'file' => 'FA25_HO25_EQ_CS_FTBL_ARG_TDF_Abasto.xlsx'], ['order' => '2000000676', 'file' => 'VIEGAS_4T_ACC25_FA25_HO25_EQ_SG_ARG.xlsx'], ['order' => '2000000677', 'file' => 'FA25_HO25_EQ_CS_FTBL_ARG_TDF_Cordoba.xlsx'], ['order' => '2000000678', 'file' => 'TOYOS_FA25_HO25_FTW_CS_NIKE_SB_ARG_Toyos_20_11_2024.xlsx'], ['order' => '2000000679', 'file' => 'TOYOS_FA25_HO25_FTW_SG_ARG.xlsx'], ['order' => '2000000682', 'file' => 'FA25_HO25_DABRA_-_DX_M_6.xlsx'], ['order' => '2000000684', 'file' => 'FA25_HO25_FTW_SG_ARG_newsport_sin_duda_de_bb_2.xlsx'], ['order' => '2000000689', 'file' => 'FA25_HO25_APP_SG_ARG_newsport.xlsx'], ['order' => '2000000690', 'file' => 'FA25_HO25_EQ_SG_ARG_newsport.xlsx'], ['order' => '2000000691', 'file' => 'FA25_HO25_APP_SG_ARG_3_.xlsx'], ['order' => '2000000692', 'file' => 'FA25_HO25_APP_AS_ARG_3_.xlsx'], ['order' => '2000000693', 'file' => 'FA25_HO25_EQ_SG_ARG_4_.xlsx'], ['order' => '2000000694', 'file' => 'FA25_HO25_EQ_AS_ARG_2_.xlsx'], ['order' => '2000000695', 'file' => 'FA25_HO25_DABRA_-_DX_M_8.xlsx'], ['order' => '2000000696', 'file' => 'FA25_HO25_FTW_SG_ARG_4_.xlsx'], ['order' => '2000000697', 'file' => 'FA25_HO25_FTW_AS_ARG_reducido_3.xlsx'], ['order' => '2000000698', 'file' => 'FA25_HO25_APP_SG_ARG_3__1.xlsx'], ['order' => '2000000699', 'file' => 'FA25_HO25_APP_AS_ARG_3__1.xlsx'], ['order' => '2000000700', 'file' => 'FA25_HO25_EQ_SG_ARG_4__1.xlsx'], ['order' => '2000000701', 'file' => 'FA25_HO25_EQ_AS_ARG_2__1.xlsx'], ['order' => '2000000702', 'file' => 'cart-product_EQ_ARG.xlsx'], ['order' => '2000000703', 'file' => 'FA25_HO25_DABRA_-_DX_L_2.xlsx'], ['order' => '2000000704', 'file' => 'FA25_HO25_EQ_NSP_ARG.xlsx'], ['order' => '2000000705', 'file' => 'FA25_HO25_DABRA_-_DX_L_3.xlsx'], ['order' => '2000000706', 'file' => 'FA25_HO25_FTW_SG_ARG_4__1.xlsx'], ['order' => '2000000707', 'file' => 'NSO_-_Planilla_EQ_HO25_-_ok_para_subir.xlsx'], ['order' => '2000000708', 'file' => 'NSO_-_Planilla_FW_HO25_-_ok_para_subir.xlsx'], ['order' => '2000000709', 'file' => 'Faltantes_NSO_FTW_Arg.xlsx'], ['order' => '2000000710', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_XXL.xlsx'], ['order' => '2000000711', 'file' => 'FA25_HO25_DABRA_-_DX_L_36.xlsx'], ['order' => '2000000712', 'file' => 'AMI_FA25_HO25_APP_SG_ARG_AMI.xlsx'], ['order' => '2000000713', 'file' => 'AMI_FA25_HO25_EQ_SG_ARG_AMI.xlsx'], ['order' => '2000000714', 'file' => 'AMI_FA25_HO25_FTW_SG_ARG_AMI.xlsx'], ['order' => '2000000715', 'file' => 'FA25_HO25_DABRA_-_DX_L_37.xlsx'], ['order' => '2000000716', 'file' => 'ANNEX_DIGITAL_FA25_HO25_FTW_AS_ARG_ONLINE.xlsx'], ['order' => '2000000717', 'file' => 'ANNEX_DIGITAL_FA25_HO25_APP_AS_ARG_ONLINE.xlsx'], ['order' => '2000000718', 'file' => 'FA25_HO25_APP_SG_ARG_3__2.xlsx'], ['order' => '2000000719', 'file' => 'ANNEX_FA25_HO25_APP_AS_ARG_FISICOS.xlsx'], ['order' => '2000000720', 'file' => 'ANNEX_FA25_HO25_EQ_AS_ARG_FISICOS.xlsx'], ['order' => '2000000721', 'file' => 'FA25_HO25_DABRA_-_DX_L_39.xlsx'], ['order' => '2000000722', 'file' => 'ANNEX_FA25_HO25_FTW_AS_ARG.xlsx'], ['order' => '2000000723', 'file' => 'ANNEX_SG_FA25_HO25_APP_SG_ARG_ONLINE.xlsx'], ['order' => '2000000724', 'file' => 'ANNEX_SG_FA25_HO25_EQ_SG_ARG_ONLINE.xlsx'], ['order' => '2000000725', 'file' => 'ANNEX_SG_FA25_HO25_FTW_SG_ARG_ONLINE.xlsx'], ['order' => '2000000726', 'file' => 'FA25_HO25_DABRA_-_DX_L_30.xlsx'], ['order' => '2000000727', 'file' => 'ESSENTIAL_FA25_HO25_APP_SG_ARG_ESSENTIAL.xlsx'], ['order' => '2000000728', 'file' => 'ESSENTIAL_FA25_HO25_EQ_SG_ARG_ESSENTIAL.xlsx'], ['order' => '2000000729', 'file' => 'ESSENTIAL_FA25_HO25_FTW_SG_ARG_ESSENTIAL.xlsx'], ['order' => '2000000731', 'file' => 'NBA_FA25_HO25_APP_CS_BSKT_ARG_NBA.xlsx'], ['order' => '2000000732', 'file' => 'NBA_FA25_HO25_EQ_CS_BSKT_ARG_NBA.xlsx'], ['order' => '2000000733', 'file' => 'NBA_FA25_HO25_FTW_CS_BSKT_ARG.xlsx'], ['order' => '2000000734', 'file' => 'NSO_-_Planilla_APP_HO25_-_ok_para_subir.xlsx'], ['order' => '2000000735', 'file' => 'FA25_HO25_DABRA_-_DX_L_44.xlsx'], ['order' => '2000000736', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_XL.xlsx'], ['order' => '2000000737', 'file' => 'FA25_HO25_DABRA_-_DX_L_46.xlsx'], ['order' => '2000000738', 'file' => 'FA25_HO25_EQ_SG_ARG_4__2.xlsx'], ['order' => '2000000741', 'file' => 'FA25_HO25_DABRA_-_DX_L_47.xlsx'], ['order' => '2000000742', 'file' => 'cart-product_AP_ARG_1.xlsx'], ['order' => '2000000743', 'file' => 'FA25_HO25_DABRA_-_DX_M_9.xlsx'], ['order' => '2000000744', 'file' => 'FA25_HO25_FTW_SG_ARG_OK_PARA_SUBIR_diferencias_2.xlsx'], ['order' => '2000000747', 'file' => 'Planilla_FTW_NDDC_Argentina.xlsx'], ['order' => '2000000748', 'file' => 'FA25_HO25_DABRA_-_DX_XL.xlsx'], ['order' => '2000000749', 'file' => 'Planilla_FTW_NDDC_Argentina_2.xlsx'], ['order' => '2000000751', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_DESPOSITO.xlsx'], ['order' => '2000000753', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_DIGITAL_1.xlsx'], ['order' => '2000000756', 'file' => 'SM_cart-product_FW.xlsx'], ['order' => '2000000758', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_L_1.xlsx'], ['order' => '2000000759', 'file' => 'FA25_HO25_DABRA_-_DX_XL_2.xlsx'], ['order' => '2000000760', 'file' => 'FA25_HO25_DABRA_-_DX_M_13.xlsx'], ['order' => '2000000761', 'file' => 'FA25_HO25_DABRA_-_SC_DIGITAL.xlsx'], ['order' => '2000000762', 'file' => 'FA25_HO25_DABRA_-_SC_XL_1.xlsx'], ['order' => '2000000763', 'file' => 'FA25_HO25_EQ_CS_RUN_ARG.xlsx'], ['order' => '2000000764', 'file' => 'planilla_FA25_HO25_FTW_SG_ARG_open_MDQ.xlsx'], ['order' => '2000000766', 'file' => 'planilla_FA25_HO25_FTW_SG_ARG_open_stadio_uno_1.xlsx'], ['order' => '2000000767', 'file' => 'Planilla_FA25_HO25_FTW_AS_ARG_TRIP_MDQ.xlsx'], ['order' => '2000000768', 'file' => 'Planilla_FA25_HO25_FTW_AS_ARG_TRIP_STADIO_UNO.xlsx'], ['order' => '2000000769', 'file' => 'FA25_HO25_FTW_SG_ARG_4__2.xlsx'], ['order' => '2000000770', 'file' => 'FA25_HO25_FTW_AS_ARG_2__2.xlsx'], ['order' => '2000000771', 'file' => 'FA25_HO25_FTW_AS_ARG_reemplazo_cortez_caida.xlsx'], ['order' => '2000000772', 'file' => 'EQ_NVS_FA_HO25_ARG_1.xlsx'], ['order' => '2000000773', 'file' => 'FA25_HO25_APP_SG_ARG_3_.XLSX'], ['order' => '2000000774', 'file' => 'FA25_HO25_APP_AS_ARG_3_.XLSX'], ['order' => '2000000775', 'file' => 'FA25_HO25_EQ_SG_ARG_4__3.xlsx'], ['order' => '2000000776', 'file' => 'FA25_HO25_EQ_AS_ARG_2__2.xlsx'], ['order' => '2000000778', 'file' => 'Adds_Argentina_AP_NDDC_rebuys.xlsx'], ['order' => '2000000779', 'file' => 'FA25_HO25_FTW_SG_ARG_4__3.xlsx'], ['order' => '2000000780', 'file' => 'Adds_Argentina_NDDC.xlsx'], ['order' => '2000000781', 'file' => 'FA25_HO25_APP_SG_ARG_3__3.xlsx'], ['order' => '2000000782', 'file' => 'FA25_HO25_EQ_SG_ARG_4__4.xlsx'], ['order' => '2000000783', 'file' => 'Pedido_Nike_Calzado_MATEU_Q4_2025_-_FA25_HO25_FTW_SG_ARG_1.xlsx'], ['order' => '2000000784', 'file' => 'Pedido_Nike_Calzado_AURELIUS_Q4_2025_-_FA25_HO25_FTW_AS_ARG.xlsx'], ['order' => '2000000787', 'file' => 'Planilla_Nike_Indumentaria_MATEU_Armado_de_curvas_-_FA25_HO25_APP_SG_ARG.xlsx'], ['order' => '2000000788', 'file' => 'Planilla_Nike_Indumentaria_Aurelius_Armado_Curvas_-_FA25_HO25_APP_AS_ARG.xlsx'], ['order' => '2000000789', 'file' => 'Pedido_Nike_Accesorios_AURELIUS_Q4_2025_-_FA25_HO25_EQ_AS_ARG_AURELIUS_.xlsx'], ['order' => '2000000790', 'file' => 'FA25_HO25_EQ_SG_ARG_-_STADIO_UNO_1.xlsx'], ['order' => '2000000791', 'file' => 'FA25_HO25_EQ_SG_ARG_3.xlsx'], ['order' => '2000000792', 'file' => 'FA25_HO25_EQ_AS_ARG_-_STADIO_UNO.xlsx'], ['order' => '2000000793', 'file' => 'FA25_HO25_EQ_AS_ARG_1.xlsx'], ['order' => '2000000794', 'file' => 'MDQ_Planilla_FA25_HO25_FTW_CS_RUN_ARG_RUNBIKE.xlsx'], ['order' => '2000000795', 'file' => 'FA25_HO25_DABRA_-_SC_XL_5.xlsx'], ['order' => '2000000797', 'file' => 'FA25_HO25_DABRA_-_SC_M.xlsx'], ['order' => '2000000798', 'file' => 'FA25_HO25_DABRA_-_SC_M_1.xlsx'], ['order' => '2000000801', 'file' => 'FA25_HO25_DABRA_-_SC_M_2.xlsx'], ['order' => '2000000808', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_L_9.xlsx'], ['order' => '2000000810', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_L_10.xlsx'], ['order' => '2000000811', 'file' => 'Pedido_Nike_Accesorios_Mateu_Q4_2025_-_FA25_HO25_EQ_SG_ARG.xlsx'], ['order' => '2000000812', 'file' => 'Pedido_Unidades_AJ1_AF1_y_Dunk_Aurelius_-_de_FA25_HO25_FTW_AS_ARG.xlsx'], ['order' => '2000000813', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_L_11.xlsx'], ['order' => '2000000815', 'file' => 'Copia_de_FA25_HO25_FTW_SG_ARG_2_.xlsx'], ['order' => '2000000817', 'file' => 'Copia_de_FA25_HO25_APP_SG_ARG.xlsx'], ['order' => '2000000818', 'file' => 'Copia_de_FA25_HO25_EQ_SG_ARG.xlsx'], ['order' => '2000000819', 'file' => 'Copia_de_FA25_HO25_FTW_AS_ARG.xlsx'], ['order' => '2000000821', 'file' => 'Copia_de_FA25_HO25_APP_AS_ARG.xlsx'], ['order' => '2000000822', 'file' => 'Copia_de_FA25_HO25_EQ_AS_ARG.xlsx'], ['order' => '2000000823', 'file' => 'Copia_de_FA25_HO25_FTW_CS_BSKT_ARG.xlsx'], ['order' => '2000000824', 'file' => 'Copia_de_FA25_HO25_APP_CS_BSKT_ARG.xlsx'], ['order' => '2000000825', 'file' => 'Copia_de_FA25_HO25_EQ_CS_BSKT_ARG.xlsx'], ['order' => '2000000826', 'file' => 'cart-product_EQ_pasion.xlsx'], ['order' => '2000000828', 'file' => 'cart-product_AP_pasion_3.xlsx'], ['order' => '2000000829', 'file' => 'cart-product_FW_pasion.xlsx'], ['order' => '2000000830', 'file' => 'cart-product_FW_parque.xlsx'], ['order' => '2000000831', 'file' => 'FA25_HO25_MOOV_AS_ARG_-_M_2.xlsx'], ['order' => '2000000832', 'file' => 'cart-product_AP_parque.xlsx'], ['order' => '2000000833', 'file' => 'cart-product_EQ_parque.xlsx'], ['order' => '2000000834', 'file' => 'cart-product_FW_consumer_experience.xlsx'], ['order' => '2000000835', 'file' => 'cart-product_AP_consumer_experience.xlsx'], ['order' => '2000000836', 'file' => 'FA25_HO25_APP_AS_ARG_3__2.xlsx'], ['order' => '2000000838', 'file' => 'FA25_HO25_EQ_SG_ARG_4__5.xlsx'], ['order' => '2000000840', 'file' => 'AJ_AF_faltantes_HO25.xlsx'], ['order' => '2000000841', 'file' => 'cart-product_FW_consumer_experience_.xlsx'], ['order' => '2000000842', 'file' => 'FW_NVS_FA_HO25_ARG_1.xlsx'], ['order' => '2000000843', 'file' => 'FA25_HO25_FTW_SG_ARG_6.xlsx'], ['order' => '2000000844', 'file' => 'Nike_FA25_HO25_FTW_AS_ARG_20-11-24.xlsx'], ['order' => '2000000846', 'file' => 'Nike_FA25_HO25_FTW_AS_ARG_25-11-24_ADICIONAL_.xlsx'], ['order' => '2000000847', 'file' => 'FA25_HO25_DABRA_-_DX_XXL_-_santi.xlsx'], ['order' => '2000000848', 'file' => 'FA25_HO25_FTW_AS_ARG_Cero26_AF1_1.xlsx'], ['order' => '2000000850', 'file' => 'FA25_HO25_FTW_SG_ARG_9.xlsx']
            // ['order' => '4000000193', 'file' => 'cart-product_3__1.xlsx'], ['order' => '4000000194', 'file' => 'FA25_HO25_FTW_SG_URU_1_.xlsx'], ['order' => '4000000195', 'file' => 'cart-product_EQ_URU.xlsx'], ['order' => '4000000196', 'file' => 'cart-product_AP_URU.xlsx'], ['order' => '4000000197', 'file' => '20241121_-_FA25_HO25_EQP_SG_URU_-_Laniban_SA_Puerta_1.xlsx'], ['order' => '4000000198', 'file' => '20241121_-_FA25_HO25_EQP_SG_URU_-_Laniban_SA_Puerta_2.xlsx'], ['order' => '4000000199', 'file' => '20241121_-_FA25_HO25_APP_SG_URU_-_Laniban_SA_Puerta_1.xlsx'], ['order' => '4000000200', 'file' => 'PEDIDO_NSO_URU_EQP_3.xlsx'], ['order' => '4000000201', 'file' => 'PEDIDO_NSO_URU_APP.xlsx'], ['order' => '4000000202', 'file' => 'COMPRA_FTW_NIKE_PORTONES_HO25_3.xlsx'], ['order' => '4000000203', 'file' => 'COMPRA_FTW_NIKE_PUNTA_CARRETAS_HO25.xlsx'], ['order' => '4000000204', 'file' => 'COMPRA_NIKE_PUNTA_CARRETAS_EQP_HO25.xlsx'], ['order' => '4000000205', 'file' => 'COMPRA_EQP_NIKE_PORTONES_HO25.xlsx'], ['order' => '4000000206', 'file' => 'COMPRA_APP_LAS_ZAPAS_MONTEVIDEO_SHOPPING_H025_1.xlsx'], ['order' => '4000000207', 'file' => 'COMPRA_APP_LAS_ZAPAS_NUEVOCENTRO_SHOPPING_H025.xlsx'], ['order' => '4000000208', 'file' => 'COMPRA_APP_KICKS_PUNTA_DEL_ESTE_H025.xlsx'], ['order' => '4000000209', 'file' => 'COMPRA_APP_LAS_ZAPAS_WEB_H025.xlsx'], ['order' => '4000000210', 'file' => 'COMPRA_EQP_LAS_ZAPAS_MONTEVIDEO_HO25.xlsx'], ['order' => '4000000211', 'file' => 'COMPRA_EQP_LAS_ZAPAS_NUEVOCENTRO_HO25.xlsx'], ['order' => '4000000212', 'file' => 'COMPRA_EQP_KICKS_PUNTA_DEL_ESTE_HO25.xlsx'], ['order' => '4000000213', 'file' => 'COMPRA_EQP_LAS_ZAPAS_WEB_HO25.xlsx'], ['order' => '4000000214', 'file' => 'COMPRA_EQP_LA_CANCHA_MONTEVIDEO_HO25.xlsx'], ['order' => '4000000215', 'file' => 'COMPRA_EQP_LA_CANCHA_NUEVOCENTRO_HO25.xlsx'], ['order' => '4000000216', 'file' => 'COMPRA_EQP_LA_CANCHA_TRES_CRUCES_HO25.xlsx'], ['order' => '4000000217', 'file' => 'COMPRA_EQP_LA_CANCHA_PORTONES_HO25.xlsx'], ['order' => '4000000218', 'file' => 'COMPRA_EQP_LA_CANCHA_BLACK_HO25.xlsx'], ['order' => '4000000219', 'file' => 'COMPRA_EQP_LA_CANCHA_WEB_HO25.xlsx'], ['order' => '4000000220', 'file' => 'COMPRA_EQP_SPORTLINE_PUNTA_DEL_ESTE_HO25.xlsx'], ['order' => '4000000221', 'file' => 'COMPRA_EQP_SPORTLINE_ATLANTICO_HO25.xlsx'], ['order' => '4000000222', 'file' => 'COMPRA_EQP_SPORTLINE_KIDS_HO25.xlsx'], ['order' => '4000000223', 'file' => 'COMPRA_APP_LA_CANCHA_MONTEVIDEO_HO25.xlsx'], ['order' => '4000000224', 'file' => 'COMPRA_APP_LA_CANCHA_NUEVOCENTRO_HO25.xlsx'], ['order' => '4000000225', 'file' => 'COMPRA_APP_LA_CANCHA_TRES_CRUCES_HO25.xlsx'], ['order' => '4000000226', 'file' => 'COMPRA_APP_LA_CANCHA_PORTONES_HO25.xlsx'], ['order' => '4000000227', 'file' => 'COMPRA_APP_LA_CANCHA_BLACK_HO25.xlsx'], ['order' => '4000000228', 'file' => 'COMPRA_APP_LA_CANCHA_WEB_HO25.xlsx'], ['order' => '4000000229', 'file' => 'COMPRA_APP_SPORTLINE_PUNTA_HO25.xlsx'], ['order' => '4000000230', 'file' => 'COMPRA_APP_SPORTLINE_ATLANTICO_HO25.xlsx'], ['order' => '4000000231', 'file' => 'COMPRA_APP_SPORTLINE_KIDS_HO25.xlsx'], ['order' => '4000000232', 'file' => '20241121_-_FA25_HO25_FTW_SG_URU_-_Laniban_SA_Puerta_1.xlsx'], ['order' => '4000000233', 'file' => '20241121_-_FA25_HO25_FTW_SG_URU_-_Laniban_SA_Puerta_2.xlsx'], ['order' => '4000000234', 'file' => 'cart-product_1_.xlsx'], ['order' => '4000000235', 'file' => 'DARIOSTAR_FA25_HO25_FTW_SG_URU.xlsx'], ['order' => '4000000236', 'file' => 'COMPRA_APP_NIKE_SHOP_PUNTA_CARRETAS_HO25_3.xlsx'], ['order' => '4000000237', 'file' => 'COMPRA_APP_NIKE_PORTONES_HO25_5.xlsx'], ['order' => '4000000238', 'file' => 'PEDIDO_FA25_HO25_FTW_SG_URU_1.xlsx'], ['order' => '4000000239', 'file' => 'FA25_HO25_APP_SG_URU_2.xlsx'], ['order' => '4000000240', 'file' => 'FA25_HO25_EQ_SG_URU_6.xlsx'], ['order' => '4000000241', 'file' => 'FA25_HO25_FTW_SG_URU_1.xlsx'], ['order' => '4000000242', 'file' => 'FA25_HO25_APP_SG_URU_puerta_2_.xlsx'], ['order' => '4000000243', 'file' => 'FA25_HO25_EQ_SG_URU_puerta_2_.xlsx'], ['order' => '4000000244', 'file' => 'FA25_HO25_FTW_SG_URU_puerta2_.xlsx'], ['order' => '4000000245', 'file' => 'cart-product_3__2.xlsx'], ['order' => '4000000246', 'file' => 'EQ_NVS_URU_HO25_ATENEA_1.xlsx'], ['order' => '4000000247', 'file' => 'AP_NVS_URU_HO25_ATENEA.xlsx'], ['order' => '4000000248', 'file' => 'FA25_HO25_APP_SG_URU_3.xlsx'], ['order' => '4000000249', 'file' => 'FA25_HO25_FTW_SG_URU_2.xlsx'], ['order' => '4000000251', 'file' => 'cart-product_FW_URU.xlsx'], ['order' => '4000000252', 'file' => 'LABIGOLD_FA25_HO25_APP_SG_URU.xlsx'], ['order' => '4000000253', 'file' => 'LABIGOLD_FA25_HO25_FTW_SG_URU.xlsx'], ['order' => '4000000254', 'file' => 'LABIGOLD_FA25_HO25_EQ_SG_URU_1.xlsx'], ['order' => '4000000255', 'file' => 'Laniban_4_FA25_HO25_FTW_SG_URU.xlsx'], ['order' => '4000000256', 'file' => 'COMPRA_FTW_LAS_ZAPAS_MONTEVIDEO_1.xlsx'], ['order' => '4000000257', 'file' => 'COMPRA_FTW_LAS_ZAPAS_NUJEVOCENTRO.xlsx'], ['order' => '4000000258', 'file' => 'COMPRA_FTW_KICKS_PUNTA.xlsx'], ['order' => '4000000259', 'file' => 'COMPRA_FTW_LAS_ZAPAS_WEB.xlsx'], ['order' => '4000000260', 'file' => 'COMPRA_FTW_SPORTLINE_KIDS.xlsx'], ['order' => '4000000261', 'file' => 'COMPRA_LA_CANCHA_MONTEVIDEO_FTW_HO25_3.xlsx'], ['order' => '4000000262', 'file' => 'COMPRA_LA_CANCHA_NUEVOCENTRO_FTW_HO25_2.xlsx'], ['order' => '4000000263', 'file' => 'COMPRA_LA_CANCHA_TRES_CRUCES_FTW_HO25.xlsx'], ['order' => '4000000264', 'file' => 'COMPRA_LA_CANCHA_PORTONES_FTW_HO25.xlsx'], ['order' => '4000000265', 'file' => 'COMPRA_LA_CANCHA_BLACK_FTW_HO25_2.xlsx'], ['order' => '4000000266', 'file' => 'COMPRA_LA_CANCHA_WEB_FTW_HO25_1.xlsx'], ['order' => '4000000267', 'file' => 'COMPRA_SPORTLINE_PUNTA_FTW_HO25_1.xlsx'], ['order' => '4000000268', 'file' => 'COMPRA_SPORTLINE_ATLANTICO_FTW_HO25.xlsx'], ['order' => '4000000269', 'file' => 'COMPRA_SPORTLINE_KIDS_FTW_HO25_1.xlsx'], ['order' => '4000000270', 'file' => 'PEDIDO_NSO_URU_EQP_CAPS.xlsx'], ['order' => '4000000271', 'file' => 'PEDIDO_NSO_URU_FTW.xlsx']
            // ['order'=>'8000000010','file'=>'FA25_HO25_FTW_FRONTERA_BORDER_NEUTAX.xlsx'],['order'=>'8000000011','file'=>'FA25_HO25_APP_FRONTERA_BORDER_NEUTAX.xlsx'],['order'=>'8000000012','file'=>'FA25_HO25_EQ_FRONTERA_BORDER_NEUTAX_1.xlsx'],['order'=>'8000000013','file'=>'FA25_HO25_FTW_2024.11_BOREMIX.xlsx'],['order'=>'8000000015','file'=>'FA25_HO25_APP_2024.11_BOREMIX.xlsx'],['order'=>'8000000016','file'=>'FA25_HO25_EQ_2024.11_BOREMIX.xlsx']
            ['order' => '10000000001', 'file' => 'FA25_HO25_APP_FRONTERA_DUTY_LONDON_1.xlsx'], ['order' => '10000000002', 'file' => 'FA25_HO25_EQ_FRONTERA_DUTY_LONDON_1.xlsx'], ['order' => '10000000003', 'file' => 'FA25_HO25_FTW_FRONTERA_DUTY_LONDON.xlsx'], ['order' => '10000000004', 'file' => 'FA25_HO25_APP_FRONTERA_DUTY.xlsx'], ['order' => '10000000005', 'file' => 'FA25_HO25_FTW_FRONTERA_DUTY_00000002_.xlsx'], ['order' => '10000000006', 'file' => 'CASA_MARIANA_FA25_HO25_EQ_FRONTERA_DUTY.xlsx'], ['order' => '10000000007', 'file' => 'FA25_HO25_FTW_FRONTERA_DUTY.xlsx'], ['order' => '10000000008', 'file' => 'FA25_HO25_EQ_FRONTERA_DUTY.xlsx'], ['order' => '10000000009', 'file' => 'FA25_HO25_APP_FRONTERA_DUTY_1.xlsx']
        ];

        $output->writeln('<info>Aplicando fix...</info>');
        $store_id = $input->getArgument('store_id');
        $folder = $input->getArgument('folder');
        $flow = $input->getArgument('flow');

        $output->writeln('<info>Store id: [' . $store_id . ']</info>');
        $output->writeln('<info>Folder: [' . $folder . ']</info>');
        $output->writeln('<info>Flow: [' . $flow . ']</info>');

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        /**
         * @var \Magento\Store\Model\StoreManagerInterface $store
         */
        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $store = $store->getStore($store_id);

        $output->writeln('<info>Store name: ' . $store->getName() . '</info>');

        /**
         * @var \Southbay\CustomCheckout\Helper\UploadCardData $helper
         */
        $helper = $objectManager->get('Southbay\CustomCheckout\Helper\UploadCardData');

        foreach ($items as $_item) {
            $file = $_item['file'];
            $cart_content = $helper->getCardFromFile($folder . '/' . $file, $store);
            $cart_items = [];
            $map = [];
            $output->writeln('<info>Cart file: ' . $file . '</info>');

            foreach ($cart_content as $key => $data) {
                $map[$key] = $data['first_product_variant'];
                if ($data['request']['qty'] > 0) {
                    $cart_items[$key] = $data['request'];
                }
            }

            $order = $this->getOrderItems($_item['order'], $connection);
            $diff = $this->compareOrderVsCart($order, $cart_items);

            foreach ($diff as $sku => $errors) {
                $output->writeln('order: #' . $_item['order'] . '. sku: ' . $sku . '. ' . $errors['msg']);
                $this->fix($_item['order'], $sku, $map[$sku], $errors, $store_id);
            }
        }

        $output->writeln('<info>Fin aplicacion fix.</info>');

        return 1;
    }

    private function compareOrderVsCart($order, $cart_items)
    {
        $result = [];

        foreach ($cart_items as $key => $data) {
            if (isset($order[$key])) {
                $result[$key] = $this->flowDiff($key, $order[$key], $data);
            } else {
                if ($data['qty'] > 0 && isset($data['month_delivery_date_3']) && $this->sumFlowValues($data['month_delivery_date_3']) > 0) {
                    $result[$key] = [
                        'msg' => 'not exists',
                        'type' => 'sku',
                        'key' => 'month_delivery_date_3',
                        'data' => $data['month_delivery_date_3'],
                        'qty' => $this->sumFlowValues($data['month_delivery_date_3'])
                    ];
                }
            }
        }

        $_result = [];

        foreach ($result as $key => $errors) {
            if (!empty($errors)) {
                $_result[$key] = $errors;
            }
        }

        return $_result;
    }

    private function flowDiff($sku, $order, $cart_item)
    {
        $result = [];

        foreach ($cart_item as $key => $content) {
            $result[$key] = [];
            if ($key == 'month_delivery_date_1'
                || $key == 'month_delivery_date_2'
                || $key == 'month_delivery_date_3') {
                if (isset($order[$key])) {
                    foreach ($content as $size => $value) {
                        if ($value <= 0) {
                            continue;
                        }
                        if (!isset($order[$key][$size])) {
                            // $result[$sku][] = 'size not found: ' . $size . '. flow: ' . $key;
                        } else if ($order[$key][$size] < $value) {
                            // $result[$sku][] = 'size diff value. cart value: ' . $value . '. order value: ' . $order[$key][$size] . '. size: ' . $size . '. flow: ' . $key;
                        }
                    }
                } else {
                    if ($this->sumFlowValues($content) > 0) {
                        // $result[$sku][] = 'flow not exists: ' . $key;
                        $result[$key] = [
                            'msg' => 'flow not exists: ' . $key,
                            'type' => 'flow',
                            'key' => $key,
                            'data' => $content,
                            'qty' => $this->sumFlowValues($content)
                        ];
                    }
                }
            }
        }

        $_result = [];

        foreach ($result as $key => $errors) {
            if (!empty($errors)) {
                $_result[$key] = $errors;
            }
        }

        return $_result;
    }

    private function getOrderItems($order, $connection)
    {
        $result = [];

        $sql = "select item.sku, item.qty_ordered, item.product_options from sales_order o inner join sales_order_item item on item.order_id = o.entity_id where o.increment_id = '$order'";
        $rows = $connection->fetchAll($sql);

        foreach ($rows as $row) {
            if (!empty($row['product_options'])) {
                $product_options = json_decode($row['product_options'], true);
                $sku = $row['sku'];
                $sku = explode('/', $sku)[0];
                if (isset($product_options['info_buyRequest'])
                    && (
                        isset($product_options['info_buyRequest']['month_delivery_date_1']) ||
                        isset($product_options['info_buyRequest']['month_delivery_date_2']) ||
                        isset($product_options['info_buyRequest']['month_delivery_date_3'])
                    )) {
                    $result[$sku] = $product_options['info_buyRequest'];
                }
            }
        }

        return $result;
    }

    private function sumFlowValues($data)
    {
        $values = array_values($data);
        return array_sum($values);
    }

    /**
     * @throws \Exception
     */
    private function fix($order_nro, $sku, $product, $error, $store_id)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        $itemFactory = $objectManager->get('Magento\Sales\Model\Order\ItemFactory');

        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('increment_id', ['eq' => $order_nro]);
        $collection->addFieldToFilter('store_id', ['eq' => $store_id]);
        $collection->addFieldToFilter('status', ['eq' => 'pending']);
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $collection->getFirstItem();

        if ($order == null) {
            throw new \Exception('Order not found ' . $order_nro);
        }

//        $product = $error['data']['product'];

        if ($error['type'] == 'flow') {
            $items = $order->getItems();

            foreach ($items as $item) {
                if ($item->getProductId() != $product->getId()) {
                    continue;
                }

                $options = $item->getProductOption();
                $options['info_buyRequest']['month_delivery_date_3'] = $error['data'];

                $item->setQtyOrdered($item->getQtyOrdered() + $error['qty']);
                $item->setRowTotal($item->getQtyOrdered() * $product->getPrice());
                $item->setBaseRowTotal($item->getRowTotal());
                $item->setRowTotalInclTax($item->getRowTotal());
                $item->setBaseRowTotalInclTax($item->getRowTotal());
                $item->setProductOption($options);

                break;
            }
        } else if ($error['type'] == 'sku') {
            /**
             * @var \Magento\Sales\Model\Order\Item $item
             */
            $item = $itemFactory->create();
            $item->setProductId($product->getId());
            $item->setSku($product->getSku());
            $item->setName($product->getName());
            $item->setProductType($product->getTypeId());

            $item->setQtyOrdered($error['qty']);

            $item->setPrice($product->getPrice());
            $item->setOriginalPrice($product->getPrice());
            $item->setBasePrice($product->getPrice());
            $item->setBaseOriginalPrice($product->getPrice());
            $item->setPriceInclTax($product->getPrice());
            $item->setBasePriceInclTax($product->getPrice());

            $item->setRowTotal($product->getPrice() * $error['qty']);
            $item->setBaseRowTotal($item->getRowTotal());
            $item->setRowTotalInclTax($item->getRowTotal());
            $item->setBaseRowTotalInclTax($item->getRowTotal());

            $item->setProductOptions($this->createProductOptions($error['data'], $product->getId(), $error['qty']));

            $order->addItem($item);
        } else {
            throw new \Exception('Invalid error type for order ' . $order_nro);
        }

        $order->save();
    }

    private function createProductOptions($data, $product_id, $qty)
    {
        $result = [
            'info_buyRequest' => ['month_delivery_date_1' => [], 'month_delivery_date_2' => [], 'month_delivery_date_3' => $data],
            "form_key" => 0,
            "qty" => $qty,
            "product" => $product_id
        ];

        foreach ($data as $size => $value) {
            $result['info_buyRequest']['month_delivery_date_1'][$size] = 0;
            $result['info_buyRequest']['month_delivery_date_2'][$size] = 0;
        }

        return $result;
    }
}
