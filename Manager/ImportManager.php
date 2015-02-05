<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\user;
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
     * @var array
     */
    private $availableFormats = [];

    /**
     * @param \Icap\PortfolioBundle\Importer\ImporterInterface[] $importers
     */
    public function __construct(array $importers = [])
    {
        if (0 === count($importers)) {
            $importers = [
                new Leap2aImporter()
            ];
        }

        foreach ($importers as $importer) {
            $this->importers[$importer->getFormat()] = $importer;
            $this->availableFormats[$importer->getFormat()] = $importer->getFormatLabel();
        }
    }

    /**
     * @return array
     */
    public function getAvailableFormats()
    {
        return $this->availableFormats;
    }

    /**
     * @param      $content
     * @param user $user
     * @param      $format
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio|null
     * @throws \Exception
     */
    public function simulateImport($content, User $user, $format)
    {
        if (!isset($this->importers[$format])) {
            throw new \Exception(sprintf("No importer for the '%s' format.", $format));
        }

        return $this->importers[$format]->import($content, $user);
    }
}
