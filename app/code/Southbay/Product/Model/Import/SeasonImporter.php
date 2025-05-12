<?php

namespace Southbay\Product\Model\Import;

class SeasonImporter
{
    private $seasonFactory;
    private $seasonRepository;

    public function __construct(\Southbay\Product\Model\SeasonFactory                   $seasonFactory,
                                \Southbay\Product\Model\ResourceModel\Season\Collection $seasonRepository)
    {
        $this->seasonFactory = $seasonFactory;
        $this->seasonRepository = $seasonRepository;
    }


}
