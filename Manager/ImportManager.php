<?php

namespace Icap\PortfolioBundle\Manager;

use Icap\PortfolioBundle\Importer\Importer;
use Icap\PortfolioBundle\Importer\ImporterInterface;
use Icap\PortfolioBundle\Importer\Leap2aImporter;

class ImportManager
{
    /**
     * @var \Icap\PortfolioBundle\Importer\ImporterInterface[]
     */
    private $importers;

    /**
     * @param \Icap\PortfolioBundle\Importer\ImporterInterface[] $importers
     */
    public function __construct(array $importers = array())
    {
        if (0 === count($importers)) {
            $importers = [
                new Leap2aImporter()
            ];
        }

        $this->importers = $importers;
    }

    /**
     * @return array
     */
    public function getAvailableImportFormats()
    {
        $availableImportFormats = [];

        foreach ($this->importers as $importer) {
            $availableImportFormats[$importer->getFormat()] = $importer->getFormatLabel();
        }

        return $availableImportFormats;
    }
}
