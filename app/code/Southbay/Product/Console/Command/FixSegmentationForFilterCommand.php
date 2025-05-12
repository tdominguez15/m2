<?php

namespace Southbay\Product\Console\Command;

use Southbay\Product\Model\Import\ProductImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixSegmentationForFilterCommand extends Command
{
    protected function configure()
    {
        $this->setName('southbay:fix:segmentation-filter')
            ->setDescription('Fix product segmentation filter');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $p = new ProductImporter();
        $p->runSetup();

        $file = __DIR__ . '/fix-segmenatation.json';
        $output->writeln('reading file: ' . $file);

        $content = file_get_contents($file);
        $data = json_decode($content, true);
        $output->writeln('total data: ' . count($data));

        /**
         * @var \Magento\Eav\Api\Data\AttributeInterface $attr
         */
        // $attr = $p->findAttributeByCode('southbay_segmentation');
        // $segmentation_id = $attr->getAttributeId();

        // $output->writeln('segmentation_id: ' . $segmentation_id);

        /**
         * @var \Magento\Eav\Api\Data\AttributeInterface $attr
         */
        $attr = $p->findAttributeByCode('southbay_channel_level_list');
        $channel_level_id = $attr->getAttributeId();

        $output->writeln('channel_level_id: ' . $channel_level_id);

        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var \Southbay\Product\Block\Adminhtml\Options\SegmentationOptionsDataProvider $segmentations_options
         */
        $segmentations_options = $objectManager->get('Southbay\Product\Block\Adminhtml\Options\SegmentationOptionsDataProvider');
        $options = $segmentations_options->toOptionArray();
        $_options = [];

        foreach ($options as $option) {
            $_options[] = $option['value'];
        }

        $options = $_options;

        /**
         * @var \Magento\Framework\App\ResourceConnection $resource
         */
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();
        $cache = [];

        foreach ($data as $item) {
            $sku = $item['sku'];
            $sql = "SELECT entity_id FROM catalog_product_entity WHERE sku LIKE '$sku'";
            $list = $connection->fetchAll($sql);

            foreach ($list as $_entity_id) {
                $entity_id = $_entity_id['entity_id'];
            }
        }

        return 1;
    }
}
