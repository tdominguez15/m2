<?php

namespace Southbay\Product\Console\Command;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Southbay\Product\Api\Data\SeasonInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCreateSeasonCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('southbay:test:create:season')->setDescription('Create test season');
    }

    protected function executeOLD(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $_repository = $objectManager->get('Southbay\Product\Model\ResourceModel\SeasonRepository');
        $season = $_repository->findCurrent();
        $output->writeln('season: ' . $season->getMonthDeliveryDate1());

        /**
         * @var \Magento\Framework\Stdlib\DateTime\Timezone $timezone
         */
        $timezone = $objectManager->get('Magento\Framework\Stdlib\DateTime\Timezone');
        $fecha = new \DateTime($season->getMonthDeliveryDate1());

        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore(0);
        // $store = $objectManager->get('Magento\Framework\Locale\Resolver');

        $formattedFecha = $timezone->date($fecha,$store->getLocale())->format('M-y');

        $output->writeln('locale: '. $store->getLocale());
        $output->writeln($formattedFecha);
        $output->writeln(json_encode(__($formattedFecha)->getText()));

        $formatter = new \IntlDateFormatter($store->getLocale(), \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        $formatter->setPattern('MMM');

        $output->writeln($fecha->format("M"));
        $output->writeln($formatter->format($fecha));
        $output->writeln(json_encode($_repository->getMonthForDeliveryFromCurrent($store->getLocale())));

        return 1;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Magento\Framework\App\ObjectManager $objectManager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $category = $objectManager->create('Magento\Catalog\Model\Category');
        $factory = $objectManager->get(\Southbay\Product\Model\SeasonFactory::class);
        $_category = $category->getCollection()->addAttributeToFilter('name', 'Temporada2025v8')->getFirstItem();

        /**
         * @var \Southbay\Product\Model\ResourceModel\Season $repository
         */
        // $repository = $objectManager->get(\Southbay\Product\Model\ResourceModel\Season::class);
        $_repository = $objectManager->get('Southbay\Product\Model\ResourceModel\SeasonRepository');

        try {
            $year = '2025';
            /**
             * @var \Southbay\Product\Model\Season $model
             */
            $model = $factory->create();
            $model->setSeasonTypeCode('001');
            $model->setSeasonCode($year);
            $model->setSeasonName('Temporada ' . $year);
            $model->setSeasonCategoryId($_category->getId());
            $model->setSeasonDescription('Temporada de verano ' . $year);

            $model->setMonthDeliveryDate1('2025-01-03');
            $model->setMonthDeliveryDate2('2025-02-03');
            $model->setMonthDeliveryDate3('2025-03-03');

            $output->writeln('Nueva temporada creada');
        } catch (AlreadyExistsException $e) {
            $output->writeln('Ya existe la temporada');
        }

        return 1;
    }
}
